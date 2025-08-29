<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'brand'])->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::where('module_type', 'peluqueria')
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->get();

        $brands = Brand::where('is_active', true)
                      ->orderBy('name')
                      ->get();

        $suppliers = \App\Models\HairdressingSupplier::where('is_active', true)
                      ->orderBy('name')
                      ->get();

        return view('products.create', compact('categories', 'brands', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id', // Agregamos validación para brand_id
            'supplier_name' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products',
            'current_stock' => 'nullable|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('module_type', 'peluqueria')
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->get();

        $brands = Brand::where('is_active', true)
                      ->orderBy('name')
                      ->get();

        $suppliers = \App\Models\HairdressingSupplier::where('is_active', true)
                      ->orderBy('name')
                      ->get();

        return view('products.edit', compact('product', 'categories', 'brands', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id', // Agregamos validación para brand_id
            'supplier_name' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $product->id,
            'current_stock' => 'nullable|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente');
    }

    // Método opcional para obtener marcas por categoría vía AJAX
    public function getBrandsByCategory($categoryId)
    {
        $brands = Brand::whereHas('categories', function($query) use ($categoryId) {
            $query->where('categories.id', $categoryId);
        })->where('is_active', true)
          ->orderBy('name')
          ->get();

        return response()->json($brands);
    }
}
