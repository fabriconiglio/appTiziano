<?php

namespace App\Http\Controllers;

use App\Models\SupplierInventory;
use App\Models\DistributorCategory;
use App\Models\DistributorBrand;
use Illuminate\Http\Request;
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

        $inventories = $query->latest()->paginate(10);

        // Para los filtros en la vista
        $categories = SupplierInventory::distinct('category')->pluck('category');
        $suppliers = SupplierInventory::distinct('supplier_name')->pluck('supplier_name');

        return view('supplier-inventories.index', compact('inventories', 'categories', 'suppliers'));
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
            ->orderBy('product_name', 'asc') // Ordenar por nombre del producto
            ->orderBy('distributor_brand_id', 'asc') // Luego por marca
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

        // Ordenar productos para priorizar coincidencias de marca
        $products = $products->sortBy(function ($product) use ($query) {
            $brand = $product->distributorBrand ? $product->distributorBrand->name : '';
            $productName = $product->product_name;
            
            // Prioridad 0: Coincidencia exacta de marca
            if (stripos($brand, $query) !== false) {
                return '0_' . $productName;
            }
            
            // Prioridad 1: Coincidencia parcial de marca
            if (stripos($brand, $query) !== false) {
                return '1_' . $productName;
            }
            
            // Prioridad 2: Otros productos
            return '2_' . $productName;
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
        return view('supplier-inventories.create', compact('categories', 'brands'));
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
        return view('supplier-inventories.edit', compact('supplierInventory', 'categories', 'brands'));
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
     * Export inventory to PDF with 3 sections
     */
    public function exportToPdf()
    {
        // Obtener todos los productos con sus relaciones
        $products = SupplierInventory::with(['distributorBrand', 'distributorCategory'])
            ->orderBy('product_name')
            ->get();

        // Preparar los datos para cada sección
        $completeInventory = [];
        $mayorPrices = [];
        $menorPrices = [];

        foreach ($products as $product) {
            $description = $product->description ?: $product->product_name;
            $brand = $product->distributorBrand ? $product->distributorBrand->name : '';
            $displayText = !empty($brand) ? $description . ' - ' . $brand : $description;
            
            $completeInventory[] = [
                'name' => $product->product_name,
                'description' => $displayText,
                'precio_mayor' => $product->precio_mayor ? '$' . number_format($product->precio_mayor, 2) : 'N/A',
                'precio_menor' => $product->precio_menor ? '$' . number_format($product->precio_menor, 2) : 'N/A',
                'costo' => $product->costo ? '$' . number_format($product->costo, 2) : 'N/A'
            ];

            $mayorPrices[] = [
                'name' => $product->product_name,
                'description' => $displayText,
                'precio_mayor' => $product->precio_mayor ? '$' . number_format($product->precio_mayor, 2) : 'N/A'
            ];

            $menorPrices[] = [
                'name' => $product->product_name,
                'description' => $displayText,
                'precio_menor' => $product->precio_menor ? '$' . number_format($product->precio_menor, 2) : 'N/A'
            ];
        }

        $data = [
            'completeInventory' => $completeInventory,
            'mayorPrices' => $mayorPrices,
            'menorPrices' => $menorPrices,
            'exportDate' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('supplier-inventories.pdf', $data);
        
        return $pdf->download('inventario_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
