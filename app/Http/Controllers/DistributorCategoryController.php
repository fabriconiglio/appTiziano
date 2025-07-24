<?php

namespace App\Http\Controllers;

use App\Models\DistributorBrand;
use App\Models\DistributorCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistributorCategoryController extends Controller
{
    public function index()
    {
        $categories = DistributorCategory::with('brands')->latest()->paginate(10);
        return view('distributor_categories.index', compact('categories'));
    }

    public function create()
    {
        $brands = DistributorBrand::where('is_active', true)->orderBy('name')->get();
        return view('distributor_categories.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'is_active' => 'boolean',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:distributor_brands,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = DistributorCategory::create($validated);

        // Asociar las marcas seleccionadas
        if ($request->has('brands')) {
            $category->brands()->attach($request->brands);
        }

        return redirect()
            ->route('distributor_categories.index')
            ->with('success', 'Distributor category created successfully.');
    }

    public function show(DistributorCategory $distributorCategory)
    {
        $distributorCategory->load('brands');
        return view('distributor_categories.show', compact('distributorCategory'));
    }

    public function edit(DistributorCategory $distributorCategory)
    {
        $brands = DistributorBrand::where('is_active', true)->orderBy('name')->get();
        $selectedBrands = $distributorCategory->brands->pluck('id')->toArray();
        return view('distributor_categories.edit', compact('distributorCategory', 'brands', 'selectedBrands'));
    }

    public function update(Request $request, DistributorCategory $distributorCategory)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'is_active' => 'boolean',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:distributor_brands,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $distributorCategory->update($validated);

        // Sincronizar las marcas
        $distributorCategory->brands()->sync($request->brands ?? []);

        return redirect()
            ->route('distributor_categories.index')
            ->with('success', 'Distributor category updated successfully.');
    }

    public function destroy(DistributorCategory $distributorCategory)
    {
        // Desasociar todas las marcas antes de eliminar
        $distributorCategory->brands()->detach();
        $distributorCategory->forceDelete();

        return redirect()
            ->route('distributor_categories.index')
            ->with('success', 'Distributor category deleted successfully.');
    }
} 