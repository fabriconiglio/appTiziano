<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::with('categories')->paginate(10);
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        // Optimización: Solo traer categorías activas y ordenadas por nombre
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('brands.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:brands',
            'description' => 'nullable',
            'logo_url' => 'nullable|url',
            'logo_file' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (int) ($request->input('is_active', 0) == 1);

        // Si se sube un archivo, guardarlo y usar esa ruta
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('brand_logos', 'public');
            $validated['logo_url'] = '/storage/' . $path;
        }

        $brand = Brand::create($validated);

        // Asociar categorías
        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        }

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca creada exitosamente');
    }

    public function show(Brand $brand)
    {
        $brand->load(['categories', 'products']);
        return view('brands.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        // Optimización: Solo traer categorías activas y ordenadas por nombre
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();
        $selectedCategories = $brand->categories->pluck('id')->toArray();

        return view('brands.edit', compact('brand', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:brands,name,' . $brand->id,
            'description' => 'nullable',
            'logo_url' => 'nullable|url',
            'logo_file' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = (int) ($request->input('is_active', 0) == 1);

        // Si se sube un archivo, guardarlo y usar esa ruta
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('brand_logos', 'public');
            $validated['logo_url'] = '/storage/' . $path;
        }

        $brand->update($validated);

        // Actualizar categorías
        $brand->categories()->sync($request->categories ?? []);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca actualizada exitosamente');
    }

    public function destroy(Brand $brand)
    {
        // Desasociar las categorías antes de eliminar
        $brand->categories()->detach();
        $brand->forceDelete();

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca eliminada exitosamente');
    }
}
