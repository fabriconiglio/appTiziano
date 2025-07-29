<?php

namespace App\Http\Controllers;

use App\Models\SupplierInventory;
use App\Models\DistributorCategory;
use App\Models\DistributorBrand;
use Illuminate\Http\Request;

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
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $products = SupplierInventory::where('description', 'LIKE', "%{$query}%")
            ->orWhere('product_name', 'LIKE', "%{$query}%")
            ->orWhere('sku', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get(['id', 'product_name', 'description', 'stock_quantity', 'sku']);

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
}
