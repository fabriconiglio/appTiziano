<?php

namespace App\Http\Controllers;

use App\Models\DistributorClient;
use App\Models\SupplierInventory;
use App\Models\DistributorTechnicalRecord;
use App\Models\DistributorCurrentAccount;
use App\Models\DistributorDiscount;
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
            'products_purchased.*.price' => 'nullable|numeric|min:0',
            'products_purchased.*.original_price' => 'nullable|numeric|min:0',
            'products_purchased.*.discount_type' => 'nullable|string|in:percentage,fixed',
            'products_purchased.*.discount_value' => 'nullable|numeric|min:0',
            'products_purchased.*.discount_reason' => 'nullable|string',
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

        $validated['distributor_client_id'] = $distributorClient->getKey();
        $validated['user_id'] = auth()->id();

        // Calcular el total automáticamente basado en los productos comprados
        $calculatedTotal = 0;
        $totalDiscountAmount = 0;
        $giftProducts = [];
        $discountDetails = [];
        
        if (!empty($validated['products_purchased'])) {
            foreach ($validated['products_purchased'] as $productData) {
                $supplierInventory = SupplierInventory::find($productData['product_id']);
                if ($supplierInventory) {
                    // Usar precio con descuento si está disponible, sino calcular según tipo de compra
                    $price = 0;
                    if (!empty($productData['price']) && $productData['price'] > 0) {
                        // Usar precio con descuento aplicado
                        $price = $productData['price'];
                    } else {
                        // Determinar el precio según el tipo de compra
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
                    }
                    
                    $subtotalProduct = $price * $productData['quantity'];
                    $calculatedTotal += $subtotalProduct;
                    
                    // Buscar descuentos aplicables para este producto
                    $availableDiscounts = DistributorDiscount::valid()
                                                            ->forDistributor($distributorClient->getKey())
                                                            ->get()
                                                            ->filter(function ($discount) use ($supplierInventory) {
                                                                return $discount->appliesTo(
                                                                    $supplierInventory->sku, 
                                                                    $supplierInventory->product_name, 
                                                                    $supplierInventory->id,
                                                                    $supplierInventory->distributor_category_id,
                                                                    $supplierInventory->distributor_brand_id
                                                                );
                                                            });
                    
                    foreach ($availableDiscounts as $discount) {
                        $calculation = $discount->calculateDiscount($productData['quantity'], $price);
                        
                        // Aplicar descuento si hay monto de descuento o si es un regalo (final_price = 0)
                        if ($calculation['discount_amount'] > 0 || $calculation['final_price'] == 0) {
                            $totalDiscountAmount += $calculation['discount_amount'];
                            $discountDetails[] = [
                                'discount_id' => $discount->id,
                                'product_name' => $supplierInventory->product_name,
                                'discount_description' => $discount->description,
                                'discount_amount' => $calculation['discount_amount'],
                                'final_price' => $calculation['final_price'],
                                'message' => $calculation['message']
                            ];
                            
                            // Incrementar el uso del descuento
                            $discount->incrementUsage();
                        }
                        
                        if (!empty($calculation['gift_products'])) {
                            $giftProducts = array_merge($giftProducts, $calculation['gift_products']);
                            $discountDetails[] = [
                                'discount_id' => $discount->id,
                                'product_name' => $supplierInventory->product_name,
                                'discount_description' => $discount->description,
                                'gift_products' => $calculation['gift_products'],
                                'message' => $calculation['message']
                            ];
                            
                            // Incrementar el uso del descuento
                            $discount->incrementUsage();
                        }
                    }
                }
            }
        }
        
        // Aplicar descuentos al total
        $totalAfterDiscounts = $calculatedTotal - $totalDiscountAmount;
        $validated['total_amount'] = $totalAfterDiscounts;
        
        // Agregar información de descuentos y regalos a las observaciones
        if (!empty($discountDetails) || !empty($giftProducts)) {
            $discountInfo = "\n\n--- DESCUENTOS Y REGALOS APLICADOS ---\n";
            
            if ($totalDiscountAmount > 0) {
                $discountInfo .= "Total descuentos aplicados: $" . number_format($totalDiscountAmount, 2) . "\n";
                $discountInfo .= "Total original: $" . number_format($calculatedTotal, 2) . "\n";
                $discountInfo .= "Total con descuentos: $" . number_format($totalAfterDiscounts, 2) . "\n\n";
            }
            
            foreach ($discountDetails as $detail) {
                $discountInfo .= "• {$detail['discount_description']} ({$detail['product_name']}): {$detail['message']}\n";
                if (isset($detail['discount_amount'])) {
                    $discountInfo .= "  Descuento: $" . number_format($detail['discount_amount'], 2) . "\n";
                }
                if (isset($detail['gift_products'])) {
                    $discountInfo .= "  Regalos: " . implode(', ', $detail['gift_products']) . "\n";
                }
            }
            
            if (!empty($giftProducts)) {
                $discountInfo .= "\nRegalos totales incluidos: " . implode(', ', array_unique($giftProducts)) . "\n";
            }
            
            $validated['observations'] = ($validated['observations'] ?? '') . $discountInfo;
        }

        // Agregar información de descuentos manuales aplicados
        $manualDiscounts = [];
        if (!empty($validated['products_purchased'])) {
            foreach ($validated['products_purchased'] as $productData) {
                if (!empty($productData['discount_type']) && !empty($productData['discount_value'])) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    if ($supplierInventory) {
                        $discountType = $productData['discount_type'] === 'percentage' ? '%' : '$';
                        $discountValue = $productData['discount_value'];
                        $discountReason = $productData['discount_reason'] ?? 'Sin motivo especificado';
                        
                        $manualDiscounts[] = "• {$supplierInventory->product_name}: {$discountValue}{$discountType} - {$discountReason}";
                    }
                }
            }
        }
        
        if (!empty($manualDiscounts)) {
            $manualDiscountInfo = "\n\n--- DESCUENTOS MANUALES APLICADOS ---\n";
            $manualDiscountInfo .= implode("\n", $manualDiscounts) . "\n";
            $validated['observations'] = ($validated['observations'] ?? '') . $manualDiscountInfo;
        }

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
            'totalAfterDiscounts' => $totalAfterDiscounts,
            'balanceAdjustment' => $balanceAdjustment,
            'balanceAdjustment_type' => gettype($balanceAdjustment),
            'finalAmount_calculation' => $totalAfterDiscounts + $balanceAdjustment,
            'finalAmount' => max(0, $totalAfterDiscounts + $balanceAdjustment)
        ]);
        
        // Calcular el monto final considerando el ajuste de cuenta corriente
        $balanceAdjustment = floatval($validated['balance_adjustment'] ?? 0);
        
        // Si el balanceAdjustment es positivo, significa que tiene deuda (se suma)
        // Si el balanceAdjustment es negativo, significa que tiene crédito (se resta)
        $finalAmount = max(0, $totalAfterDiscounts + $balanceAdjustment);
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
            if ($validated['use_current_account']) {
                if ($balanceAdjustment != 0) {
                    // Si hay ajuste de cuenta corriente (cliente con crédito o deuda existente)
                    if ($balanceAdjustment < 0) {
                        // Si el cliente tiene crédito, usar el crédito y crear deuda solo por la diferencia
                        $creditUsed = abs($balanceAdjustment);
                        $remainingDebt = max(0, $totalAfterDiscounts - $creditUsed);
                        
                        if ($remainingDebt > 0) {
                            // Crear deuda solo por el monto que no se cubrió con el crédito
                            \App\Models\DistributorCurrentAccount::create([
                                'distributor_client_id' => $distributorClient->getKey(),
                                'user_id' => auth()->id(),
                                'distributor_technical_record_id' => $technicalRecord->id,
                                'type' => 'debt',
                                'amount' => $remainingDebt,
                                'description' => 'Deuda por ficha técnica de compra (después de aplicar crédito)',
                                'date' => now(),
                                'reference' => 'FT-' . $technicalRecord->id,
                                'observations' => "Ficha técnica #{$technicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra') . " - Crédito aplicado: $" . number_format($creditUsed, 2) . " - Deuda restante: $" . number_format($remainingDebt, 2)
                            ]);
                        }
                    } else {
                        // Si el cliente tiene deuda existente, solo agregar la nueva compra como deuda
                        \App\Models\DistributorCurrentAccount::create([
                            'distributor_client_id' => $distributorClient->getKey(),
                            'user_id' => auth()->id(),
                            'distributor_technical_record_id' => $technicalRecord->id,
                            'type' => 'debt',
                            'amount' => $totalAfterDiscounts,
                            'description' => 'Deuda por ficha técnica de compra',
                            'date' => now(),
                            'reference' => 'FT-' . $technicalRecord->id,
                            'observations' => "Ficha técnica #{$technicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra') . " - Deuda existente: $" . number_format($balanceAdjustment, 2) . " - Nueva compra: $" . number_format($totalAfterDiscounts, 2)
                        ]);
                    }
                } else {
                    // No hay ajuste de cuenta corriente, crear deuda normal
                    if ($finalAmount > 0) {
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
                }
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
        
        // Calcular el saldo actual sin incluir esta ficha técnica para evitar duplicación
        $currentBalance = $distributorClient->getCurrentBalance();
        // Solo restar el monto de la compra (total_amount), no el final_amount que incluye ajustes de cuenta corriente
        $currentBalanceWithoutThisRecord = $currentBalance - $distributorTechnicalRecord->total_amount;
        
        return view('distributor_technical_records.edit', compact('distributorClient', 'distributorTechnicalRecord', 'supplierInventories', 'currentBalanceWithoutThisRecord'));
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
            'products_purchased.*.price' => 'nullable|numeric|min:0',
            'products_purchased.*.original_price' => 'nullable|numeric|min:0',
            'products_purchased.*.discount_type' => 'nullable|string|in:percentage,fixed',
            'products_purchased.*.discount_value' => 'nullable|numeric|min:0',
            'products_purchased.*.discount_reason' => 'nullable|string',
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
        $totalDiscountAmount = 0;
        $giftProducts = [];
        $discountDetails = [];
        
        if (!empty($validated['products_purchased'])) {
            foreach ($validated['products_purchased'] as $productData) {
                $supplierInventory = SupplierInventory::find($productData['product_id']);
                if ($supplierInventory) {
                    // Usar precio con descuento si está disponible, sino calcular según tipo de compra
                    $price = 0;
                    if (!empty($productData['price']) && $productData['price'] > 0) {
                        // Usar precio con descuento aplicado
                        $price = $productData['price'];
                    } else {
                        // Determinar el precio según el tipo de compra
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
                    }
                    
                    $subtotalProduct = $price * $productData['quantity'];
                    $calculatedTotal += $subtotalProduct;
                    
                    // Buscar descuentos aplicables para este producto
                    $availableDiscounts = DistributorDiscount::valid()
                                                            ->forDistributor($distributorClient->getKey())
                                                            ->get()
                                                            ->filter(function ($discount) use ($supplierInventory) {
                                                                return $discount->appliesTo(
                                                                    $supplierInventory->sku, 
                                                                    $supplierInventory->product_name, 
                                                                    $supplierInventory->id,
                                                                    $supplierInventory->distributor_category_id,
                                                                    $supplierInventory->distributor_brand_id
                                                                );
                                                            });
                    
                    foreach ($availableDiscounts as $discount) {
                        $calculation = $discount->calculateDiscount($productData['quantity'], $price);
                        
                        // Aplicar descuento si hay monto de descuento o si es un regalo (final_price = 0)
                        if ($calculation['discount_amount'] > 0 || $calculation['final_price'] == 0) {
                            $totalDiscountAmount += $calculation['discount_amount'];
                            $discountDetails[] = [
                                'discount_id' => $discount->id,
                                'product_name' => $supplierInventory->product_name,
                                'discount_description' => $discount->description,
                                'discount_amount' => $calculation['discount_amount'],
                                'final_price' => $calculation['final_price'],
                                'message' => $calculation['message']
                            ];
                            
                            // Incrementar el uso del descuento
                            $discount->incrementUsage();
                        }
                        
                        if (!empty($calculation['gift_products'])) {
                            $giftProducts = array_merge($giftProducts, $calculation['gift_products']);
                            $discountDetails[] = [
                                'discount_id' => $discount->id,
                                'product_name' => $supplierInventory->product_name,
                                'discount_description' => $discount->description,
                                'gift_products' => $calculation['gift_products'],
                                'message' => $calculation['message']
                            ];
                            
                            // Incrementar el uso del descuento
                            $discount->incrementUsage();
                        }
                    }
                }
            }
        }
        
        // Aplicar descuentos al total
        $totalAfterDiscounts = $calculatedTotal - $totalDiscountAmount;
        $validated['total_amount'] = $totalAfterDiscounts;
        
        // Agregar información de descuentos y regalos a las observaciones
        if (!empty($discountDetails) || !empty($giftProducts)) {
            $discountInfo = "\n\n--- DESCUENTOS Y REGALOS APLICADOS ---\n";
            
            if ($totalDiscountAmount > 0) {
                $discountInfo .= "Total descuentos aplicados: $" . number_format($totalDiscountAmount, 2) . "\n";
                $discountInfo .= "Total original: $" . number_format($calculatedTotal, 2) . "\n";
                $discountInfo .= "Total con descuentos: $" . number_format($totalAfterDiscounts, 2) . "\n\n";
            }
            
            foreach ($discountDetails as $detail) {
                $discountInfo .= "• {$detail['discount_description']} ({$detail['product_name']}): {$detail['message']}\n";
                if (isset($detail['discount_amount'])) {
                    $discountInfo .= "  Descuento: $" . number_format($detail['discount_amount'], 2) . "\n";
                }
                if (isset($detail['gift_products'])) {
                    $discountInfo .= "  Regalos: " . implode(', ', $detail['gift_products']) . "\n";
                }
            }
            
            if (!empty($giftProducts)) {
                $discountInfo .= "\nRegalos totales incluidos: " . implode(', ', array_unique($giftProducts)) . "\n";
            }
            
            $validated['observations'] = ($validated['observations'] ?? '') . $discountInfo;
        }

        // Agregar información de descuentos manuales aplicados
        $manualDiscounts = [];
        if (!empty($validated['products_purchased'])) {
            foreach ($validated['products_purchased'] as $productData) {
                if (!empty($productData['discount_type']) && !empty($productData['discount_value'])) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    if ($supplierInventory) {
                        $discountType = $productData['discount_type'] === 'percentage' ? '%' : '$';
                        $discountValue = $productData['discount_value'];
                        $discountReason = $productData['discount_reason'] ?? 'Sin motivo especificado';
                        
                        $manualDiscounts[] = "• {$supplierInventory->product_name}: {$discountValue}{$discountType} - {$discountReason}";
                    }
                }
            }
        }
        
        if (!empty($manualDiscounts)) {
            $manualDiscountInfo = "\n\n--- DESCUENTOS MANUALES APLICADOS ---\n";
            $manualDiscountInfo .= implode("\n", $manualDiscounts) . "\n";
            $validated['observations'] = ($validated['observations'] ?? '') . $manualDiscountInfo;
        }

        // Debug: Log los valores para verificar
        Log::info('Debug Ficha Técnica UPDATE:', [
            'total_amount' => $totalAfterDiscounts,
            'total_discount_amount' => $totalDiscountAmount,
            'balance_adjustment' => $validated['balance_adjustment'] ?? 0,
            'request_data' => $request->all()
        ]);

        // Calcular el monto final considerando el ajuste de cuenta corriente
        $balanceAdjustment = floatval($validated['balance_adjustment'] ?? 0);
        
        // Si el balanceAdjustment es positivo, significa que tiene deuda (se suma)
        // Si el balanceAdjustment es negativo, significa que tiene crédito (se resta)
        $finalAmount = max(0, $totalAfterDiscounts + $balanceAdjustment);
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
            
            // Crear movimientos en cuenta corriente solo si está marcado el checkbox
            if ($validated['use_current_account']) {
                if ($balanceAdjustment != 0) {
                    // Si hay ajuste de cuenta corriente (cliente con crédito o deuda existente)
                    if ($balanceAdjustment < 0) {
                        // Si el cliente tiene crédito, usar el crédito y crear deuda solo por la diferencia
                        $creditUsed = abs($balanceAdjustment);
                        $remainingDebt = max(0, $totalAfterDiscounts - $creditUsed);
                        
                        if ($remainingDebt > 0) {
                            // Crear deuda solo por el monto que no se cubrió con el crédito
                            DistributorCurrentAccount::create([
                                'distributor_client_id' => $distributorClient->getKey(),
                                'user_id' => auth()->id(),
                                'distributor_technical_record_id' => $distributorTechnicalRecord->id,
                                'type' => 'debt',
                                'amount' => $remainingDebt,
                                'description' => 'Deuda por ficha técnica de compra (después de aplicar crédito)',
                                'date' => now(),
                                'reference' => 'FT-' . $distributorTechnicalRecord->id,
                                'observations' => "Ficha técnica #{$distributorTechnicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra') . " - Crédito aplicado: $" . number_format($creditUsed, 2) . " - Deuda restante: $" . number_format($remainingDebt, 2)
                            ]);
                        }
                    } else {
                        // Si el cliente tiene deuda existente, solo agregar la nueva compra como deuda
                        DistributorCurrentAccount::create([
                            'distributor_client_id' => $distributorClient->getKey(),
                            'user_id' => auth()->id(),
                            'distributor_technical_record_id' => $distributorTechnicalRecord->id,
                            'type' => 'debt',
                            'amount' => $totalAfterDiscounts,
                            'description' => 'Deuda por ficha técnica de compra',
                            'date' => now(),
                            'reference' => 'FT-' . $distributorTechnicalRecord->id,
                            'observations' => "Ficha técnica #{$distributorTechnicalRecord->id} - " . ($validated['purchase_type'] ?: 'Compra') . " - Deuda existente: $" . number_format($balanceAdjustment, 2) . " - Nueva compra: $" . number_format($totalAfterDiscounts, 2)
                        ]);
                    }
                } else {
                    // No hay ajuste de cuenta corriente, crear deuda normal
                    if ($finalAmount > 0) {
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
                }
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
        $manualDiscounts = [];

        if (!empty($distributorTechnicalRecord->products_purchased)) {
            foreach ($distributorTechnicalRecord->products_purchased as $productData) {
                $supplierInventory = SupplierInventory::with('distributorBrand')
                    ->find($productData['product_id']);
                
                if ($supplierInventory) {
                    $description = $supplierInventory->description ?: $supplierInventory->product_name;
                    $brand = $supplierInventory->distributorBrand ? $supplierInventory->distributorBrand->name : '';
                    $displayText = !empty($brand) ? $description . ' - ' . $brand : $description;
                    
                    // Usar precio con descuento si está disponible, sino calcular según tipo de compra
                    $unitPrice = 0;
                    $originalPrice = 0;
                    $hasDiscount = false;
                    
                    if (!empty($productData['price']) && $productData['price'] > 0) {
                        // Usar precio con descuento aplicado
                        $unitPrice = $productData['price'];
                        $originalPrice = $productData['original_price'] ?? $unitPrice;
                        $hasDiscount = !empty($productData['discount_type']) && !empty($productData['discount_value']);
                    } else {
                        // Determinar el precio según el tipo de compra
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
                        $originalPrice = $unitPrice;
                    }
                    
                    $totalPrice = $unitPrice * $productData['quantity'];
                    $originalTotalPrice = $originalPrice * $productData['quantity'];
                    
                    // Agregar información de descuento manual si existe
                    if ($hasDiscount) {
                        $discountType = $productData['discount_type'] === 'percentage' ? '%' : '$';
                        $discountValue = $productData['discount_value'];
                        $discountReason = $productData['discount_reason'] ?? 'Sin motivo especificado';
                        
                        $manualDiscounts[] = [
                            'product_name' => $supplierInventory->product_name,
                            'discount_type' => $discountType,
                            'discount_value' => $discountValue,
                            'discount_reason' => $discountReason,
                            'original_price' => $originalPrice,
                            'discounted_price' => $unitPrice,
                            'original_total' => $originalTotalPrice,
                            'discounted_total' => $totalPrice,
                            'savings' => $originalTotalPrice - $totalPrice
                        ];
                    }
                    
                    $products[] = [
                        'name' => $supplierInventory->product_name,
                        'description' => $displayText,
                        'quantity' => $productData['quantity'],
                        'unit_price' => $unitPrice,
                        'original_unit_price' => $originalPrice,
                        'total_price' => $totalPrice,
                        'original_total_price' => $originalTotalPrice,
                        'has_discount' => $hasDiscount,
                        'discount_info' => $hasDiscount ? [
                            'type' => $productData['discount_type'],
                            'value' => $productData['discount_value'],
                            'reason' => $productData['discount_reason'] ?? 'Sin motivo especificado'
                        ] : null
                    ];
                    
                    $total += $totalPrice;
                }
            }
        }

        $data = [
            'technicalRecord' => $distributorTechnicalRecord,
            'distributorClient' => $distributorTechnicalRecord->distributorClient,
            'products' => $products,
            'manualDiscounts' => $manualDiscounts,
            'generatedDate' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('distributor_technical_records.remito', $data);
        
        return $pdf->download('remito_' . $distributorTechnicalRecord->id . '_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
