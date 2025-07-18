<?php

namespace App\Http\Controllers;

use App\Models\DistributorBrand;
use App\Models\DistributorCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistributorBrandController extends Controller
{
    public function index()
    {
        $brands = DistributorBrand::with('categories')->paginate(10);
        return view('distributor_brands.index', compact('brands'));
    }

    public function create()
    {
        $categories = DistributorCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('distributor_brands.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:distributor_brands',
            'description' => 'nullable',
            'logo_url' => 'nullable|url',
            'logo_file' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'categories' => 'required|array',
            'categories.*' => 'exists:distributor_categories,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Si se sube un archivo, guardarlo y usar esa ruta
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('distributor_brand_logos', 'public');
            $validated['logo_url'] = '/storage/' . $path;
        }

        $brand = DistributorBrand::create($validated);

        // Asociar categorías
        if ($request->has('categories')) {
            $brand->categories()->sync($request->categories);
        }

        return redirect()
            ->route('distributor_brands.index')
            ->with('success', 'Marca de distribuidora creada exitosamente');
    }

    public function show(DistributorBrand $distributorBrand)
    {
        $distributorBrand->load(['categories', 'products']);
        return view('distributor_brands.show', compact('distributorBrand'));
    }

    public function edit(DistributorBrand $distributorBrand)
    {
        $categories = DistributorCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
        $selectedCategories = $distributorBrand->categories->pluck('id')->toArray();

        return view('distributor_brands.edit', compact('distributorBrand', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, DistributorBrand $distributorBrand)
    {
        $validated = $request->validate([
            'name' => 'required|max:255|unique:distributor_brands,name,' . $distributorBrand->id,
            'description' => 'nullable',
            'logo_url' => 'nullable|url',
            'logo_file' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'categories' => 'required|array',
            'categories.*' => 'exists:distributor_categories,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Si se sube un archivo, guardarlo y usar esa ruta
        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('distributor_brand_logos', 'public');
            $validated['logo_url'] = '/storage/' . $path;
        }

        $distributorBrand->update($validated);

        // Actualizar categorías
        $distributorBrand->categories()->sync($request->categories ?? []);

        return redirect()
            ->route('distributor_brands.index')
            ->with('success', 'Marca de distribuidora actualizada exitosamente');
    }

    public function destroy(DistributorBrand $distributorBrand)
    {
        // Detach categories before deleting
        $distributorBrand->categories()->detach();
        $distributorBrand->forceDelete();

        return redirect()
            ->route('distributor_brands.index')
            ->with('success', 'Distributor brand deleted successfully');
    }
}
