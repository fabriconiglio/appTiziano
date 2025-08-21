<?php

namespace App\Http\Controllers;

use App\Models\DistributorClient;
use App\Models\SupplierInventory;
use App\Models\DistributorTechnicalRecord;
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
            'advance_payment' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'products_purchased' => 'nullable|array',
            'products_purchased.*.product_id' => 'required|exists:supplier_inventories,id',
            'products_purchased.*.quantity' => 'required|integer|min:1',
            'observations' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048',
            'next_purchase_notes' => 'nullable|string'
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
                        case 'especial':
                            // Para compras especiales, usar el precio menor como base
                            $price = $supplierInventory->precio_menor ?: $supplierInventory->precio_mayor ?: 0;
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

            DB::commit();

            return redirect()->route('distributor-clients.show', $distributorClient)
                ->with('success', 'Ficha técnica de compra creada exitosamente.');

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
            'advance_payment' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'products_purchased' => 'nullable|array',
            'products_purchased.*.product_id' => 'required|exists:supplier_inventories,id',
            'products_purchased.*.quantity' => 'required|integer|min:1',
            'observations' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048',
            'next_purchase_notes' => 'nullable|string'
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
                        case 'especial':
                            // Para compras especiales, usar el precio menor como base
                            $price = $supplierInventory->precio_menor ?: $supplierInventory->precio_mayor ?: 0;
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

            DB::commit();

            return redirect()->route('distributor-clients.show', $distributorClient)
                ->with('success', 'Ficha técnica de compra actualizada exitosamente.');

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
            Log::error('Error al eliminar foto: ' . $e->getMessage());
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
                        case 'especial':
                            $unitPrice = $supplierInventory->precio_menor ?: $supplierInventory->precio_mayor ?: 0;
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
            'total' => $total,
            'generatedDate' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('distributor_technical_records.remito', $data);
        
        return $pdf->download('remito_' . $distributorTechnicalRecord->id . '_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
