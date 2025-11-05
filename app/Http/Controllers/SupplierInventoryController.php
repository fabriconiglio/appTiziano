<?php

namespace App\Http\Controllers;

use App\Models\SupplierInventory;
use App\Models\DistributorCategory;
use App\Models\DistributorBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SupplierInventory::with(['distributorCategory', 'distributorBrand']);

        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerms = explode(' ', trim($request->get('search')));
            
            $query->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    if (!empty($term)) {
                        $q->where(function($subQuery) use ($term) {
                            $subQuery->where('product_name', 'LIKE', "%{$term}%")
                                ->orWhere('sku', 'LIKE', "%{$term}%")
                                ->orWhere('description', 'LIKE', "%{$term}%")
                                ->orWhere('supplier_name', 'LIKE', "%{$term}%")
                                ->orWhere('category', 'LIKE', "%{$term}%")
                                ->orWhere('brand', 'LIKE', "%{$term}%")
                                ->orWhere('notes', 'LIKE', "%{$term}%")
                                ->orWhereHas('distributorCategory', function($catQuery) use ($term) {
                                    $catQuery->where('name', 'LIKE', "%{$term}%");
                                })
                                ->orWhereHas('distributorBrand', function($brandQuery) use ($term) {
                                    $brandQuery->where('name', 'LIKE', "%{$term}%");
                                });
                        });
                    }
                }
            });
        }

        if ($request->has('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('supplier')) {
            $query->where('supplier_name', $request->get('supplier'));
        }

        // Calcular el total de inversión antes de paginar (suma de costo * stock_quantity)
        // Usar COALESCE para manejar valores nulos en costo
        $totalInversion = $query->sum(DB::raw('COALESCE(costo, 0) * stock_quantity'));

        $inventories = $query->latest()->paginate(10);

        // Para los filtros en la vista
        $categories = SupplierInventory::distinct('category')->pluck('category');
        $suppliers = SupplierInventory::distinct('supplier_name')->pluck('supplier_name');

        return view('supplier-inventories.index', compact('inventories', 'categories', 'suppliers', 'totalInversion'));
    }

    /**
     * Buscar productos para AJAX
     */
    public function search(Request $request)
    {
        $query = trim($request->get('q'));
        
        if (empty($query)) {
            return response()->json([]);
        }

        // Dividir la consulta en palabras para búsqueda más flexible
        $searchTerms = explode(' ', $query);
        
        $products = SupplierInventory::with('distributorBrand')
            ->where(function($q) use ($query, $searchTerms) {
                // Búsqueda exacta de la consulta completa
                $q->where('product_name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orWhere('sku', 'LIKE', "%{$query}%")
                ->orWhere('brand', 'LIKE', "%{$query}%")
                ->orWhereHas('distributorBrand', function($brandQuery) use ($query) {
                    $brandQuery->where('name', 'LIKE', "%{$query}%");
                });
                
                // Búsqueda por palabras individuales (si hay más de una palabra)
                if (count($searchTerms) > 1) {
                    foreach ($searchTerms as $term) {
                        if (strlen($term) > 2) { // Solo términos de más de 2 caracteres
                            $q->orWhere('product_name', 'LIKE', "%{$term}%")
                            ->orWhere('description', 'LIKE', "%{$term}%")
                            ->orWhere('brand', 'LIKE', "%{$term}%")
                            ->orWhereHas('distributorBrand', function($brandQuery) use ($term) {
                                $brandQuery->where('name', 'LIKE', "%{$term}%");
                            });
                        }
                    }
                }
            })
            ->get(['id', 'product_name', 'description', 'stock_quantity', 'sku', 'distributor_brand_id', 'brand', 'precio_mayor', 'precio_menor', 'costo']);

        // Modificar los productos para incluir nombre-descripción-marca como texto de búsqueda
        $products->transform(function ($product) {
            $productName = $product->product_name;
            $description = $product->description ?: '';
            $brand = $product->distributorBrand ? $product->distributorBrand->name : $product->brand;
            
            // Crear texto de búsqueda: nombre - descripción - marca
            $displayParts = [$productName];
            
            if (!empty(trim($description))) {
                $displayParts[] = trim($description);
            }
            if (!empty(trim($brand))) {
                $displayParts[] = trim($brand);
            }
            
            $product->display_text = implode(' - ', $displayParts);
            $product->brand = $brand; // Agregar el campo brand para el frontend
            
            return $product;
        });

        // Ordenar productos con algoritmo de relevancia mejorado
        $products = $products->sortBy(function ($product) use ($query, $searchTerms) {
            $brand = $product->distributorBrand ? $product->distributorBrand->name : ($product->brand ?: '');
            $productName = $product->product_name ?: '';
            $description = $product->description ?: '';
            
            // Crear texto completo para búsqueda: NOMBRE + MARCA + DESCRIPCIÓN (en ese orden)
            $fullText = strtolower(trim($productName . ' ' . $brand . ' ' . $description));
            $queryLower = strtolower($query);
            
            // 1. MÁXIMA PRIORIDAD: Coincidencia exacta completa en cualquier campo
            if (stripos($productName, $query) !== false || 
                stripos($description, $query) !== false || 
                stripos($brand, $query) !== false || 
                stripos($fullText, $query) !== false) {
                
                // Sub-prioridad: nombre del producto > descripción > marca > texto completo
                if (stripos($productName, $query) !== false) {
                    return '0_1_' . $productName; // Coincidencia exacta en nombre
                } elseif (stripos($description, $query) !== false) {
                    return '0_2_' . $productName; // Coincidencia exacta en descripción
                } elseif (stripos($brand, $query) !== false) {
                    return '0_3_' . $productName; // Coincidencia exacta en marca
                } else {
                    return '0_4_' . $productName; // Coincidencia exacta en texto completo
                }
            }
            
            // 1.5 NUEVA PRIORIDAD ESPECIAL: Términos clave en nombre + resto en marca
            if (count($searchTerms) > 1) {
                // Buscar si los primeros términos están en el nombre y los últimos en la marca
                $nameMatchCount = 0;
                $brandMatchCount = 0;
                $nameTermsMatched = [];
                $brandTermsMatched = [];
                
                foreach ($searchTerms as $index => $term) {
                    if (strlen($term) > 1) {
                        $termLower = strtolower($term);
                        
                        // Verificar si el término está en el nombre
                        if (stripos($productName, $term) !== false) {
                            $nameMatchCount++;
                            $nameTermsMatched[] = $index;
                        }
                        
                        // Verificar si el término está en la marca
                        if (stripos($brand, $term) !== false) {
                            $brandMatchCount++;
                            $brandTermsMatched[] = $index;
                        }
                    }
                }
                
                // Si tenemos al menos 1 término en nombre y 1 en marca
                if ($nameMatchCount > 0 && $brandMatchCount > 0) {
                    // Calcular score especial basado en la distribución
                    $distributionScore = 0;
                    
                    // Bonus si los términos del nombre son los primeros de la búsqueda
                    $consecutiveNameTerms = 0;
                    for ($i = 0; $i < count($searchTerms); $i++) {
                        if (in_array($i, $nameTermsMatched)) {
                            $consecutiveNameTerms++;
                        } else {
                            break;
                        }
                    }
                    
                    // Bonus si los términos de la marca son los últimos de la búsqueda
                    $consecutiveBrandTerms = 0;
                    for ($i = count($searchTerms) - 1; $i >= 0; $i--) {
                        if (in_array($i, $brandTermsMatched)) {
                            $consecutiveBrandTerms++;
                        } else {
                            break;
                        }
                    }
                    
                    // Calcular puntaje especial
                    $distributionScore = ($consecutiveNameTerms * 25) + ($consecutiveBrandTerms * 20) + ($nameMatchCount * 15) + ($brandMatchCount * 10);
                    
                    // Si es una distribución buena (ej: "tintura 3" en nombre, "colormaster" en marca)
                    if ($consecutiveNameTerms > 0 && $consecutiveBrandTerms > 0) {
                        return '0_5_' . str_pad(1000 - $distributionScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                    }
                    
                    // Si hay buena distribución pero no perfecta
                    if ($distributionScore > 40) {
                        return '0_6_' . str_pad(1000 - $distributionScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                    }
                }
            }
            
            // 2. SEGUNDA PRIORIDAD: Secuencia de términos en nombre + marca (MÁXIMA RELEVANCIA)
            if (count($searchTerms) > 1) {
                // Verificar secuencia en NOMBRE + MARCA (texto combinado prioritario)
                $nameMarkText = strtolower(trim($productName . ' ' . $brand));
                $lastPos = -1;
                $termsInOrder = true;
                $nameMarkSequenceScore = 0;
                
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 1) {
                        $pos = stripos($nameMarkText, strtolower($term));
                        if ($pos !== false && $pos > $lastPos) {
                            $lastPos = $pos;
                            $nameMarkSequenceScore += 30; // MÁXIMO puntaje por secuencia en nombre+marca
                        } else {
                            $termsInOrder = false;
                            break;
                        }
                    }
                }
                
                if ($termsInOrder && $nameMarkSequenceScore > 0) {
                    return '1_1_' . str_pad(1000 - $nameMarkSequenceScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
                
                // Verificar si los términos aparecen en orden solo en el nombre del producto
                $nameText = strtolower($productName);
                $lastPos = -1;
                $termsInOrder = true;
                $nameSequenceScore = 0;
                
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 1) {
                        $pos = stripos($nameText, strtolower($term));
                        if ($pos !== false && $pos > $lastPos) {
                            $lastPos = $pos;
                            $nameSequenceScore += 20; // Alto puntaje por secuencia solo en nombre
                        } else {
                            $termsInOrder = false;
                            break;
                        }
                    }
                }
                
                if ($termsInOrder && $nameSequenceScore > 0) {
                    return '1_2_' . str_pad(1000 - $nameSequenceScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
                
                // Verificar secuencia en marca (antes que descripción)
                $brandText = strtolower($brand);
                $lastPos = -1;
                $termsInOrder = true;
                $brandSequenceScore = 0;
                
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 1) {
                        $pos = stripos($brandText, strtolower($term));
                        if ($pos !== false && $pos > $lastPos) {
                            $lastPos = $pos;
                            $brandSequenceScore += 10; // Puntaje medio por secuencia en marca
                        } else {
                            $termsInOrder = false;
                            break;
                        }
                    }
                }
                
                if ($termsInOrder && $brandSequenceScore > 0) {
                    return '1_3_' . str_pad(1000 - $brandSequenceScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
                
                // Verificar secuencia en descripción (menor prioridad)
                $descText = strtolower($description);
                $lastPos = -1;
                $termsInOrder = true;
                $descSequenceScore = 0;
                
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 1) {
                        $pos = stripos($descText, strtolower($term));
                        if ($pos !== false && $pos > $lastPos) {
                            $lastPos = $pos;
                            $descSequenceScore += 5; // Menor puntaje por secuencia en descripción
                        } else {
                            $termsInOrder = false;
                            break;
                        }
                    }
                }
                
                if ($termsInOrder && $descSequenceScore > 0) {
                    return '1_4_' . str_pad(1000 - $descSequenceScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
                
                // Verificar secuencia en texto completo (última opción)
                $lastPos = -1;
                $termsInOrder = true;
                $fullSequenceScore = 0;
                
                foreach ($searchTerms as $term) {
                    if (strlen($term) > 1) {
                        $pos = stripos($fullText, strtolower($term));
                        if ($pos !== false && $pos > $lastPos) {
                            $lastPos = $pos;
                            $fullSequenceScore += 3; // Menor puntaje por secuencia en texto completo
                        } else {
                            $termsInOrder = false;
                            break;
                        }
                    }
                }
                
                if ($termsInOrder && $fullSequenceScore > 0) {
                    return '1_5_' . str_pad(1000 - $fullSequenceScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
            }
            
            // 3. TERCERA PRIORIDAD: Todos los términos presentes con énfasis en NOMBRE + MARCA
            $allTermsFound = true;
            $termMatchScore = 0;
            $nameTerms = 0;
            $brandTerms = 0;
            $descTerms = 0;
            
            foreach ($searchTerms as $term) {
                if (strlen($term) > 1) {
                    $termFound = false;
                    
                    // Verificar en nombre del producto (MÁXIMO peso)
                    if (stripos($productName, $term) !== false) {
                        $termFound = true;
                        $termMatchScore += 20; // Incrementado de 15 a 20
                        $nameTerms++;
                    }
                    // Verificar en marca (SEGUNDO peso más alto)
                    if (stripos($brand, $term) !== false) {
                        if (!$termFound) $termMatchScore += 15; // Incrementado de 5 a 15
                        $termFound = true;
                        $brandTerms++;
                    }
                    // Verificar en descripción (peso menor)
                    if (stripos($description, $term) !== false) {
                        if (!$termFound) $termMatchScore += 5; // Reducido de 8 a 5
                        $termFound = true;
                        $descTerms++;
                    }
                    
                    if (!$termFound) {
                        $allTermsFound = false;
                    }
                }
            }
            
            // Si todos los términos están presentes
            if ($allTermsFound && count($searchTerms) > 1) {
                // MÁXIMO BONUS: términos en nombre Y marca (perfecta combinación)
                if ($nameTerms > 0 && $brandTerms > 0) {
                    $termMatchScore += 50; // Incrementado de 25 a 50 para máxima prioridad
                    return '2_1_' . str_pad(1000 - $termMatchScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
                // Segundo bonus: solo términos en nombre
                elseif ($nameTerms > 0) {
                    $termMatchScore += 25;
                    return '2_2_' . str_pad(1000 - $termMatchScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
                // Tercer bonus: solo términos en marca
                elseif ($brandTerms > 0) {
                    $termMatchScore += 15;
                    return '2_3_' . str_pad(1000 - $termMatchScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
                // Último: términos solo en descripción
                else {
                    return '2_4_' . str_pad(1000 - $termMatchScore, 4, '0', STR_PAD_LEFT) . '_' . $productName;
                }
            }
            
            // 4. CUARTA PRIORIDAD: Coincidencias parciales priorizando NOMBRE + MARCA
            $nameMatches = 0;
            $brandMatches = 0;
            $descMatches = 0;
            
            foreach ($searchTerms as $term) {
                if (strlen($term) > 1) {
                    if (stripos($productName, $term) !== false) $nameMatches++;
                    if (stripos($brand, $term) !== false) $brandMatches++;
                    if (stripos($description, $term) !== false) $descMatches++;
                }
            }
            
            // Calcular score priorizando NOMBRE + MARCA
            if ($nameMatches > 0 && $brandMatches > 0) {
                // MÁXIMA PRIORIDAD: Nombre + marca
                return '3_1_' . str_pad(100 - ($nameMatches * 15 + $brandMatches * 10), 3, '0', STR_PAD_LEFT) . '_' . $productName;
            } elseif ($nameMatches > 0) {
                // SEGUNDA PRIORIDAD: Solo en nombre
                return '3_2_' . str_pad(100 - ($nameMatches * 15), 3, '0', STR_PAD_LEFT) . '_' . $productName;
            } elseif ($brandMatches > 0) {
                // TERCERA PRIORIDAD: Solo en marca
                return '3_3_' . str_pad(100 - ($brandMatches * 10), 3, '0', STR_PAD_LEFT) . '_' . $productName;
            } elseif ($descMatches > 0) {
                // ÚLTIMA PRIORIDAD: Solo en descripción
                return '3_4_' . str_pad(100 - ($descMatches * 5), 3, '0', STR_PAD_LEFT) . '_' . $productName;
            }
            
            // 5. BAJA PRIORIDAD: Otros productos
            return '9_' . $productName;
        })->values();
        
        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = DistributorCategory::where('is_active', true)->orderBy('name')->get();
        $brands = DistributorBrand::where('is_active', true)->orderBy('name')->get();
        $suppliers = \App\Models\Supplier::where('is_active', true)->orderBy('name')->get();
        return view('supplier-inventories.create', compact('categories', 'brands', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:supplier_inventories',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'supplier_email' => 'nullable|email|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'last_restock_date' => 'nullable|date',
            'status' => 'nullable|string|in:available,low_stock,out_of_stock',
            'notes' => 'nullable|string',
            'distributor_category_id' => 'nullable|exists:distributor_categories,id',
            'distributor_brand_id' => 'nullable|exists:distributor_brands,id',
            'precio_mayor' => 'nullable|numeric|min:0',
            'precio_menor' => 'nullable|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
        ]);

        // Establecer el estado basado en el stock
        if (!isset($validated['status'])) {
            if ($validated['stock_quantity'] <= 0) {
                $validated['status'] = 'out_of_stock';
            } elseif ($validated['stock_quantity'] <= 5) {
                $validated['status'] = 'low_stock';
            } else {
                $validated['status'] = 'available';
            }
        }

        SupplierInventory::create($validated);

        return redirect()->route('supplier-inventories.index')
            ->with('success', 'Producto de inventario registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SupplierInventory $supplierInventory)
    {
        return view('supplier-inventories.show', compact('supplierInventory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SupplierInventory $supplierInventory)
    {
        $categories = DistributorCategory::where('is_active', true)->orderBy('name')->get();
        $brands = DistributorBrand::where('is_active', true)->orderBy('name')->get();
        $suppliers = \App\Models\Supplier::where('is_active', true)->orderBy('name')->get();
        return view('supplier-inventories.edit', compact('supplierInventory', 'categories', 'brands', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupplierInventory $supplierInventory)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50|unique:supplier_inventories,sku,' . $supplierInventory->id,
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'supplier_email' => 'nullable|email|max:255',
            'supplier_phone' => 'nullable|string|max:20',
            'last_restock_date' => 'nullable|date',
            'status' => 'nullable|string|in:available,low_stock,out_of_stock',
            'notes' => 'nullable|string',
            'distributor_category_id' => 'nullable|exists:distributor_categories,id',
            'distributor_brand_id' => 'nullable|exists:distributor_brands,id',
            'precio_mayor' => 'nullable|numeric|min:0',
            'precio_menor' => 'nullable|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
        ]);

        // Actualizar el estado basado en el stock
        if (!isset($validated['status'])) {
            if ($validated['stock_quantity'] <= 0) {
                $validated['status'] = 'out_of_stock';
            } elseif ($validated['stock_quantity'] <= 5) {
                $validated['status'] = 'low_stock';
            } else {
                $validated['status'] = 'available';
            }
        }

        $supplierInventory->update($validated);

        return redirect()->route('supplier-inventories.index')
            ->with('success', 'Producto de inventario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupplierInventory $supplierInventory)
    {
        $supplierInventory->delete();
        return redirect()->route('supplier-inventories.index')
            ->with('success', 'Producto de inventario eliminado exitosamente.');
    }

    /**
     * Adjust stock quantity.
     */
    public function adjustStock(Request $request, SupplierInventory $supplierInventory)
    {
        $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'nullable|string'
        ]);

        $oldQuantity = $supplierInventory->stock_quantity;
        $newQuantity = $oldQuantity + $request->adjustment;

        // No permitir stock negativo
        if ($newQuantity < 0) {
            return back()->with('error', 'El ajuste resultaría en un stock negativo.');
        }

        $supplierInventory->stock_quantity = $newQuantity;

        // Actualizar fecha de reabastecimiento si es un incremento
        if ($request->adjustment > 0) {
            $supplierInventory->last_restock_date = now();
        }

        // Actualizar el estado
        if ($newQuantity <= 0) {
            $supplierInventory->status = 'out_of_stock';
        } elseif ($newQuantity <= 5) {
            $supplierInventory->status = 'low_stock';
        } else {
            $supplierInventory->status = 'available';
        }

        $supplierInventory->save();

        // Aquí podrías registrar el movimiento en una tabla de movimientos si lo necesitas

        return back()->with('success', "Stock ajustado de $oldQuantity a $newQuantity unidades.");
    }

    /**
     * Get product details for API
     */
    public function getProduct(Request $request)
    {
        $productId = $request->get('product_id');
        
        $product = SupplierInventory::with('distributorBrand')
            ->find($productId);
            
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }
        
        return response()->json([
            'id' => $product->id,
            'product_name' => $product->product_name,
            'description' => $product->description,
            'precio_mayor' => $product->precio_mayor,
            'precio_menor' => $product->precio_menor,
            'costo' => $product->costo,
            'stock_quantity' => $product->stock_quantity,
            'brand' => $product->distributorBrand ? $product->distributorBrand->name : null
        ]);
    }

    /**
     * Export inventory to Excel with 3 sheets
     */
    public function exportToExcel()
    {
        // Obtener todos los productos con sus relaciones
        $products = SupplierInventory::with(['distributorBrand', 'distributorCategory'])
            ->orderBy('product_name')
            ->get();

        // Crear el archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Hoja 1: Nombre, Descripción - Marca, Precio Mayor y Menor, Costo
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Inventario Completo');
        $sheet1->setCellValue('A1', 'Nombre del Producto');
        $sheet1->setCellValue('B1', 'Descripción - Marca');
        $sheet1->setCellValue('C1', 'Precio por Mayor');
        $sheet1->setCellValue('D1', 'Precio por Menor');
        $sheet1->setCellValue('E1', 'Costo');
        
        $row = 2;
        foreach ($products as $product) {
            $description = $product->description ?: $product->product_name;
            $brand = $product->distributorBrand ? $product->distributorBrand->name : '';
            $displayText = !empty($brand) ? $description . ' - ' . $brand : $description;
            
            $sheet1->setCellValue('A' . $row, $product->product_name);
            $sheet1->setCellValue('B' . $row, $displayText);
            $sheet1->setCellValue('C' . $row, $product->precio_mayor ? '$' . number_format($product->precio_mayor, 2) : 'N/A');
            $sheet1->setCellValue('D' . $row, $product->precio_menor ? '$' . number_format($product->precio_menor, 2) : 'N/A');
            $sheet1->setCellValue('E' . $row, $product->costo ? '$' . number_format($product->costo, 2) : 'N/A');
            $row++;
        }
        
        // Hoja 2: Nombre, Descripción - Marca, Precio Mayor
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Precios por Mayor');
        $sheet2->setCellValue('A1', 'Nombre del Producto');
        $sheet2->setCellValue('B1', 'Descripción - Marca');
        $sheet2->setCellValue('C1', 'Precio por Mayor');
        
        $row = 2;
        foreach ($products as $product) {
            $description = $product->description ?: $product->product_name;
            $brand = $product->distributorBrand ? $product->distributorBrand->name : '';
            $displayText = !empty($brand) ? $description . ' - ' . $brand : $description;
            
            $sheet2->setCellValue('A' . $row, $product->product_name);
            $sheet2->setCellValue('B' . $row, $displayText);
            $sheet2->setCellValue('C' . $row, $product->precio_mayor ? '$' . number_format($product->precio_mayor, 2) : 'N/A');
            $row++;
        }
        
        // Hoja 3: Nombre, Descripción - Marca, Precio Menor
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Precios por Menor');
        $sheet3->setCellValue('A1', 'Nombre del Producto');
        $sheet3->setCellValue('B1', 'Descripción - Marca');
        $sheet3->setCellValue('C1', 'Precio por Menor');
        
        $row = 2;
        foreach ($products as $product) {
            $description = $product->description ?: $product->product_name;
            $brand = $product->distributorBrand ? $product->distributorBrand->name : '';
            $displayText = !empty($brand) ? $description . ' - ' . $brand : $description;
            
            $sheet3->setCellValue('A' . $row, $product->product_name);
            $sheet3->setCellValue('B' . $row, $displayText);
            $sheet3->setCellValue('C' . $row, $product->precio_menor ? '$' . number_format($product->precio_menor, 2) : 'N/A');
            $row++;
        }
        
        // Ajustar ancho de columnas automáticamente
        foreach ([$sheet1, $sheet2, $sheet3] as $sheet) {
            $maxColumn = $sheet === $sheet1 ? 'E' : 'C';
            foreach (range('A', $maxColumn) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
        
        // Crear el archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'inventario_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = storage_path('app/public/' . $filename);
        
        $writer->save($filepath);
        
        // Descargar el archivo
        return response()->download($filepath, $filename)->deleteFileAfterSend();
    }

    /**
     * Export lista mayorista to PDF
     */
    public function exportListaMayorista()
    {
        // Obtener todos los productos con sus relaciones
        $products = SupplierInventory::with(['distributorBrand', 'distributorCategory'])
            ->orderBy('product_name')
            ->get();

        // Preparar los datos para lista mayorista
        $mayoristaData = [];

        foreach ($products as $product) {
            $description = $product->description ?: $product->product_name;
            $brand = $product->distributorBrand ? $product->distributorBrand->name : '';
            $displayText = !empty($brand) ? $description . ' - ' . $brand : $description;
            $category = $product->distributorCategory ? $product->distributorCategory->name : 'Sin categoría';
            
            $mayoristaData[] = [
                'name' => $product->product_name,
                'description' => $displayText,
                'precio_mayor' => $product->precio_mayor ? '$' . number_format($product->precio_mayor, 2) : 'N/A',
                'category' => $category
            ];
        }

        $data = [
            'products' => $mayoristaData,
            'exportDate' => now()->format('d/m/Y H:i:s'),
            'title' => 'Lista de Precios por Mayor'
        ];

        $pdf = Pdf::loadView('supplier-inventories.lista-mayorista', $data);
        
        return $pdf->download('lista_mayorista_' . date('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Export lista minorista to PDF
     */
    public function exportListaMinorista()
    {
        // Obtener todos los productos con sus relaciones
        $products = SupplierInventory::with(['distributorBrand', 'distributorCategory'])
            ->orderBy('product_name')
            ->get();

        // Preparar los datos para lista minorista
        $minoristaData = [];

        foreach ($products as $product) {
            $description = $product->description ?: $product->product_name;
            $brand = $product->distributorBrand ? $product->distributorBrand->name : '';
            $displayText = !empty($brand) ? $description . ' - ' . $brand : $description;
            $category = $product->distributorCategory ? $product->distributorCategory->name : 'Sin categoría';
            
            $minoristaData[] = [
                'name' => $product->product_name,
                'description' => $displayText,
                'precio_menor' => $product->precio_menor ? '$' . number_format($product->precio_menor, 2) : 'N/A',
                'category' => $category
            ];
        }

        $data = [
            'products' => $minoristaData,
            'exportDate' => now()->format('d/m/Y H:i:s'),
            'title' => 'Lista de Precios por Menor'
        ];

        $pdf = Pdf::loadView('supplier-inventories.lista-minorista', $data);
        
        return $pdf->download('lista_minorista_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
