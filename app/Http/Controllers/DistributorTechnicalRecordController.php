<?php

namespace App\Http\Controllers;

use App\Models\DistributorClient;
use App\Models\SupplierInventory;
use App\Models\DistributorTechnicalRecord;
use App\Models\DistributorCurrentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * @property DistributorClient $distributorClient
 */
class DistributorTechnicalRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DistributorClient $distributorClient)
    {
        $supplierInventories = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->orderBy('description', 'asc')
            ->orderBy('product_name', 'asc')
            ->get();
        return view('distributor_technical_records.create', compact('distributorClient', 'supplierInventories'));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param Request $request
     * @param DistributorClient $distributorClient
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, DistributorClient $distributorClient)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'purchase_type' => 'nullable|string',
            'total_amount' => 'nullable|numeric|min:0',
            'balance_adjustment' => 'nullable|numeric',
            'payment_method' => 'nullable|string',
            'use_current_account' => 'nullable|boolean',
            'use_current_account_hidden' => 'nullable|string',
            'products_purchased' => 'nullable|array',
            'products_purchased.*.product_id' => 'required|exists:supplier_inventories,id',
            'products_purchased.*.quantity' => 'required|integer|min:1',
            'observations' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048',
            'next_purchase_notes' => 'nullable|string'
        ]);

        // Asegurar que use_current_account siempre tenga un valor usando el campo oculto
        $validated['use_current_account'] = ($request->input('use_current_account_hidden') === '1');
        
        // Debug: Log para verificar el campo use_current_account
        Log::info('Debug use_current_account:', [
            'request_has_use_current_account' => $request->has('use_current_account'),
            'use_current_account_hidden_value' => $request->input('use_current_account_hidden'),
            'use_current_account_final_value' => $validated['use_current_account'],
            'all_request_data' => $request->all()
        ]);

        // Procesar las fotos
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('distributor-photos', 'public');
                $photos[] = $path;
            }
            $validated['photos'] = $photos;
        }

        $validated['distributor_client_id'] = $distributorClient->id;
        $validated['user_id'] = auth()->id();

        // Calcular el total automáticamente basado en los productos comprados
        $calculatedTotal = 0;
        if (!empty($validated['products_purchased'])) {
            foreach ($validated['products_purchased'] as $productData) {
                $supplierInventory = SupplierInventory::find($productData['product_id']);
                if ($supplierInventory) {
                    // Determinar el precio según el tipo de compra
                    $price = 0;
                    switch ($validated['purchase_type']) {
                        case 'al_por_mayor':
                            $price = $supplierInventory->precio_mayor ?: 0;
                            break;
                        case 'al_por_menor':
                            $price = $supplierInventory->precio_menor ?: 0;
                            break;
                        default:
                            // Si no se especifica tipo, usar precio menor
                            $price = $supplierInventory->precio_menor ?: $supplierInventory->precio_mayor ?: 0;
                            break;
                    }
                    $calculatedTotal += $price * $productData['quantity'];
                }
            }
        }
        $validated['total_amount'] = $calculatedTotal;

        // Debug: Log los valores para verificar
        Log::info('Debug Ficha Técnica STORE:', [
            'total_amount' => $calculatedTotal,
            'balance_adjustment' => $validated['balance_adjustment'] ?? 0,
            'balance_adjustment_type' => gettype($validated['balance_adjustment'] ?? 0),
            'request_data' => $request->all()
        ]);

        // Calcular el monto final considerando el ajuste de cuenta corriente
        $balanceAdjustment = floatval($validated['balance_adjustment'] ?? 0);
        
        // Debug: Log el cálculo del monto final
        Log::info('Debug Cálculo Final:', [
            'calculatedTotal' => $calculatedTotal,
            'balanceAdjustment' => $balanceAdjustment,
            'balanceAdjustment_type' => gettype($balanceAdjustment),
            'finalAmount_calculation' => $calculatedTotal + $balanceAdjustment,
            'finalAmount' => max(0, $calculatedTotal + $balanceAdjustment)
        ]);
        
        // Calcular el monto final considerando el ajuste de cuenta corriente
        $balanceAdjustment = floatval($validated['balance_adjustment'] ?? 0);
        
        // Si el balanceAdjustment es positivo, significa que tiene deuda (se suma)
        // Si el balanceAdjustment es negativo, significa que tiene crédito (se resta)
        $finalAmount = max(0, $calculatedTotal + $balanceAdjustment);
        $validated['final_amount'] = $finalAmount;

        // Iniciar transacción para asegurar consistencia
        DB::beginTransaction();
        
        try {
            // Crear la ficha técnica
            $technicalRecord = DistributorTechnicalRecord::create($validated);

            // Procesar productos comprados y actualizar stock
            if (!empty($validated['products_purchased'])) {
                foreach ($validated['products_purchased'] as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    
                    if ($supplierInventory) {
                        // Verificar stock disponible
                        if ($supplierInventory->stock_quantity < $productData['quantity']) {
                            throw new \Exception("Stock insuficiente para el producto: {$supplierInventory->product_name}. Stock disponible: {$supplierInventory->stock_quantity}");
                        }
                        
                        // Descontar stock
                        $supplierInventory->decrement('stock_quantity', $productData['quantity']);
                    }
                }
            }

            // Crear movimientos en cuenta corriente solo si está marcado el checkbox
            if ($validated['use_current_account'] && $balanceAdjustment != 0) {
                // Si hay ajuste de cuenta corriente
                if ($balanceAdjustment < 0) {
                    // Si el cliente usa crédito, crear una deuda por el monto usado
                    \App\Models\DistributorCurrentAccount::create([
                        'distributor_client_id' => $distributorClient->id,
                        'user_id' => auth()->id(),
                        'distributor_technical_record_id' => $technicalRecord->id,
                        'type' => 'debt',
                        'amount' => abs($balanceAdjustment),
                        'description' => 'Deuda por uso de crédito de cuenta corriente',
                        'date' => now(),
                        'reference' => 'FT-' . $technicalRecord->id,
                        'observations' => "Ficha técnica #{$technicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra') . " - Crédito usado: $" . number_format(abs($balanceAdjustment), 2)
                    ]);
                } else {
                    // Si el cliente aplica deuda, crear un pago por el monto aplicado
                    \App\Models\DistributorCurrentAccount::create([
                        'distributor_client_id' => $distributorClient->id,
                        'user_id' => auth()->id(),
                        'distributor_technical_record_id' => $technicalRecord->id,
                        'type' => 'payment',
                        'amount' => abs($balanceAdjustment),
                        'description' => 'Pago por aplicación de deuda de cuenta corriente',
                        'date' => now(),
                        'reference' => 'FT-' . $technicalRecord->id,
                        'observations' => "Ficha técnica #{$technicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra') . " - Deuda aplicada: $" . number_format(abs($balanceAdjustment), 2)
                    ]);
                }
            } elseif ($validated['use_current_account'] && $finalAmount > 0) {
                // Solo crear deuda si está marcado el checkbox, no hay ajuste y hay un monto final a pagar
                \App\Models\DistributorCurrentAccount::create([
                    'distributor_client_id' => $distributorClient->id,
                    'user_id' => auth()->id(),
                    'distributor_technical_record_id' => $technicalRecord->id,
                    'type' => 'debt',
                    'amount' => $finalAmount,
                    'description' => 'Deuda por ficha técnica de compra',
                    'date' => now(),
                    'reference' => 'FT-' . $technicalRecord->id,
                    'observations' => "Ficha técnica #{$technicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra')
                ]);
            }

            DB::commit();

            return redirect()->route('distributor-clients.show', $distributorClient)
                ->with('success', 'Ficha técnica de compra creada exitosamente.' . 
                    ($validated['use_current_account'] ? ' Se registró en la cuenta corriente.' : ' No se registró en la cuenta corriente.'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DistributorClient $distributorClient, $technical_record)
    {
        $distributorTechnicalRecord = DistributorTechnicalRecord::findOrFail($technical_record);
        
        // Obtener los productos comprados con sus detalles
        $productsPurchased = [];
        if (!empty($distributorTechnicalRecord->products_purchased)) {
            foreach ($distributorTechnicalRecord->products_purchased as $productData) {
                $supplierInventory = SupplierInventory::find($productData['product_id']);
                if ($supplierInventory) {
                    $productsPurchased[] = [
                        'product' => $supplierInventory,
                        'quantity' => $productData['quantity']
                    ];
                }
            }
        }

        return view('distributor_technical_records.show', compact('distributorClient', 'distributorTechnicalRecord', 'productsPurchased'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistributorClient $distributorClient, $technical_record)
    {
        $distributorTechnicalRecord = DistributorTechnicalRecord::findOrFail($technical_record);
        $supplierInventories = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->orderBy('description', 'asc')
            ->orderBy('product_name', 'asc')
            ->get();
        return view('distributor_technical_records.edit', compact('distributorClient', 'distributorTechnicalRecord', 'supplierInventories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistributorClient $distributorClient, $technical_record)
    {
        $distributorTechnicalRecord = DistributorTechnicalRecord::findOrFail($technical_record);
        
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'purchase_type' => 'nullable|string',
            'total_amount' => 'nullable|numeric|min:0',
            'balance_adjustment' => 'nullable|numeric',
            'payment_method' => 'nullable|string',
            'use_current_account' => 'nullable|boolean',
            'use_current_account_hidden' => 'nullable|string',
            'products_purchased' => 'nullable|array',
            'products_purchased.*.product_id' => 'required|exists:supplier_inventories,id',
            'products_purchased.*.quantity' => 'required|integer|min:1',
            'observations' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048',
            'next_purchase_notes' => 'nullable|string'
        ]);

        // Asegurar que use_current_account siempre tenga un valor usando el campo oculto
        $validated['use_current_account'] = ($request->input('use_current_account_hidden') === '1');
        
        // Debug: Log para verificar el campo use_current_account
        Log::info('Debug use_current_account UPDATE:', [
            'request_has_use_current_account' => $request->has('use_current_account'),
            'use_current_account_hidden_value' => $request->input('use_current_account_hidden'),
            'use_current_account_final_value' => $validated['use_current_account'],
            'all_request_data' => $request->all()
        ]);

        // Procesar nuevas fotos
        if ($request->hasFile('photos')) {
            $photos = $distributorTechnicalRecord->photos ?? [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('distributor-photos', 'public');
                $photos[] = $path;
            }
            $validated['photos'] = $photos;
        }

        // Calcular el total automáticamente basado en los productos comprados
        $calculatedTotal = 0;
        if (!empty($validated['products_purchased'])) {
            foreach ($validated['products_purchased'] as $productData) {
                $supplierInventory = SupplierInventory::find($productData['product_id']);
                if ($supplierInventory) {
                    // Determinar el precio según el tipo de compra
                    $price = 0;
                    switch ($validated['purchase_type']) {
                        case 'al_por_mayor':
                            $price = $supplierInventory->precio_mayor ?: 0;
                            break;
                        case 'al_por_menor':
                            $price = $supplierInventory->precio_menor ?: 0;
                            break;
                        default:
                            // Si no se especifica tipo, usar precio menor
                            $price = $supplierInventory->precio_menor ?: $supplierInventory->precio_mayor ?: 0;
                            break;
                    }
                    $calculatedTotal += $price * $productData['quantity'];
                }
            }
        }
        $validated['total_amount'] = $calculatedTotal;

        // Debug: Log los valores para verificar
        Log::info('Debug Ficha Técnica UPDATE:', [
            'total_amount' => $calculatedTotal,
            'balance_adjustment' => $validated['balance_adjustment'] ?? 0,
            'request_data' => $request->all()
        ]);

        // Calcular el monto final considerando el ajuste de cuenta corriente
        $balanceAdjustment = floatval($validated['balance_adjustment'] ?? 0);
        
        // Si el balanceAdjustment es positivo, significa que tiene deuda (se suma)
        // Si el balanceAdjustment es negativo, significa que tiene crédito (se resta)
        $finalAmount = max(0, $calculatedTotal + $balanceAdjustment);
        $validated['final_amount'] = $finalAmount;

        // Iniciar transacción
        DB::beginTransaction();
        
        try {
            // Restaurar stock anterior si existía
            if (!empty($distributorTechnicalRecord->products_purchased)) {
                foreach ($distributorTechnicalRecord->products_purchased as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    if ($supplierInventory) {
                        $supplierInventory->increment('stock_quantity', $productData['quantity']);
                    }
                }
            }

            // Actualizar la ficha técnica
            $distributorTechnicalRecord->update($validated);

            // Procesar nuevos productos comprados y actualizar stock
            if (!empty($validated['products_purchased'])) {
                foreach ($validated['products_purchased'] as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    
                    if ($supplierInventory) {
                        // Verificar stock disponible
                        if ($supplierInventory->stock_quantity < $productData['quantity']) {
                            throw new \Exception("Stock insuficiente para el producto: {$supplierInventory->product_name}. Stock disponible: {$supplierInventory->stock_quantity}");
                        }
                        
                        // Descontar stock
                        $supplierInventory->decrement('stock_quantity', $productData['quantity']);
                    }
                }
            }

            // Actualizar o crear movimientos en cuenta corriente
            $existingMovements = DistributorCurrentAccount::where('distributor_technical_record_id', $distributorTechnicalRecord->id)->get();
            
            // Eliminar movimientos existentes para recrearlos
            foreach ($existingMovements as $movement) {
                $movement->delete();
            }
            
            // Crear movimientos según la lógica actual solo si está marcado el checkbox
            if ($validated['use_current_account'] && $balanceAdjustment != 0) {
                // Si hay ajuste de cuenta corriente
                if ($balanceAdjustment < 0) {
                    // Si el cliente usa crédito, crear una deuda por el monto usado
                    DistributorCurrentAccount::create([
                        'distributor_client_id' => $distributorClient->id,
                        'user_id' => auth()->id(),
                        'distributor_technical_record_id' => $distributorTechnicalRecord->id,
                        'type' => 'debt',
                        'amount' => abs($balanceAdjustment),
                        'description' => 'Deuda por uso de crédito de cuenta corriente',
                        'date' => now(),
                        'reference' => 'FT-' . $distributorTechnicalRecord->id,
                        'observations' => "Ficha técnica #{$distributorTechnicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra') . " - Crédito usado: $" . number_format(abs($balanceAdjustment), 2)
                    ]);
                } else {
                    // Si el cliente aplica deuda, crear un pago por el monto aplicado
                    DistributorCurrentAccount::create([
                        'distributor_client_id' => $distributorClient->id,
                        'user_id' => auth()->id(),
                        'distributor_technical_record_id' => $distributorTechnicalRecord->id,
                        'type' => 'payment',
                        'amount' => abs($balanceAdjustment),
                        'description' => 'Pago por aplicación de deuda de cuenta corriente',
                        'date' => now(),
                        'reference' => 'FT-' . $distributorTechnicalRecord->id,
                        'observations' => "Ficha técnica #{$distributorTechnicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra') . " - Deuda aplicada: $" . number_format(abs($balanceAdjustment), 2)
                    ]);
                }
            } elseif ($validated['use_current_account'] && $finalAmount > 0) {
                // Solo crear deuda si está marcado el checkbox, no hay ajuste y hay un monto final a pagar
                DistributorCurrentAccount::create([
                    'distributor_client_id' => $distributorClient->id,
                    'user_id' => auth()->id(),
                    'distributor_technical_record_id' => $distributorTechnicalRecord->id,
                    'type' => 'debt',
                    'amount' => $finalAmount,
                    'description' => 'Deuda por ficha técnica de compra',
                    'date' => now(),
                    'reference' => 'FT-' . $distributorTechnicalRecord->id,
                    'observations' => "Ficha técnica #{$distributorTechnicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra')
                ]);
            }

            DB::commit();

            return redirect()->route('distributor-clients.show', $distributorClient)
                ->with('success', 'Ficha técnica de compra actualizada exitosamente.' . 
                    ($validated['use_current_account'] ? ' Se registró en la cuenta corriente.' : ' No se registró en la cuenta corriente.'));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistributorClient $distributorClient, $technical_record)
    {
        $distributorTechnicalRecord = DistributorTechnicalRecord::findOrFail($technical_record);
        
        // Restaurar stock al eliminar
        DB::beginTransaction();
        
        try {
            if (!empty($distributorTechnicalRecord->products_purchased)) {
                foreach ($distributorTechnicalRecord->products_purchased as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    if ($supplierInventory) {
                        $supplierInventory->increment('stock_quantity', $productData['quantity']);
                    }
                }
            }

            $distributorTechnicalRecord->delete();
            DB::commit();

            return redirect()->route('distributor-clients.show', $distributorClient)
                ->with('success', 'Ficha técnica de compra eliminada exitosamente.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al eliminar la ficha técnica: ' . $e->getMessage()]);
        }
    }

    public function deletePhoto(Request $request, DistributorClient $distributorClient, $technical_record)
    {
        $distributorTechnicalRecord = DistributorTechnicalRecord::findOrFail($technical_record);
        
        try {
            $photo = $request->input('photo');

            // Verificar que la foto existe en el array
            if (!in_array($photo, $distributorTechnicalRecord->photos ?? [])) {
                return response()->json(['message' => 'Foto no encontrada'], 404);
            }

            // Eliminar el archivo físico
            if (Storage::exists('public/' . $photo)) {
                Storage::delete('public/' . $photo);
            }

            // Actualizar el array de fotos
            $photos = array_values(array_filter($distributorTechnicalRecord->photos ?? [], function($p) use ($photo) {
                return $p !== $photo;
            }));

            // Actualizar el registro
            $distributorTechnicalRecord->update(['photos' => $photos]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar la foto'], 500);
        }
    }

    /**
     * Generate PDF remito for the technical record
     */
    public function generateRemito(DistributorClient $distributorClient, $technical_record)
    {
        $distributorTechnicalRecord = DistributorTechnicalRecord::with(['distributorClient', 'user'])
            ->findOrFail($technical_record);

        // Obtener los productos con sus detalles
        $products = [];
        $total = 0;

        if (!empty($distributorTechnicalRecord->products_purchased)) {
            foreach ($distributorTechnicalRecord->products_purchased as $productData) {
                $supplierInventory = SupplierInventory::with('distributorBrand')
                    ->find($productData['product_id']);
                
                if ($supplierInventory) {
                    $description = $supplierInventory->description ?: $supplierInventory->product_name;
                    $brand = $supplierInventory->distributorBrand ? $supplierInventory->distributorBrand->name : '';
                    $displayText = !empty($brand) ? $description . ' - ' . $brand : $description;
                    
                    // Determinar el precio según el tipo de compra
                    $unitPrice = 0;
                    switch ($distributorTechnicalRecord->purchase_type) {
                        case 'al_por_mayor':
                            $unitPrice = $supplierInventory->precio_mayor ?: 0;
                            break;
                        case 'al_por_menor':
                            $unitPrice = $supplierInventory->precio_menor ?: 0;
                            break;
                        default:
                            $unitPrice = $supplierInventory->precio_menor ?: $supplierInventory->precio_mayor ?: 0;
                            break;
                    }
                    
                    $totalPrice = $unitPrice * $productData['quantity'];
                    
                    $products[] = [
                        'name' => $supplierInventory->product_name,
                        'description' => $displayText,
                        'quantity' => $productData['quantity'],
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice
                    ];
                    
                    $total += $totalPrice;
                }
            }
        }

        $data = [
            'technicalRecord' => $distributorTechnicalRecord,
            'distributorClient' => $distributorTechnicalRecord->distributorClient,
            'products' => $products,
            'generatedDate' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('distributor_technical_records.remito', $data);
        
        return $pdf->download('remito_' . $distributorTechnicalRecord->id . '_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
