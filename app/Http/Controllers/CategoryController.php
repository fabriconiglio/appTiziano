<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Mostrar lista de categorías
     */
    public function index()
    {
        $categories = Category::with('brands')->latest()->paginate(10);
        return view('categories.index', compact('categories'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        return view('categories.create', compact('brands'));
    }

    /**
     * Almacenar nueva categoría
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'module_type' => 'required|in:peluqueria,distribuidora',
            'is_active' => 'boolean',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category = Category::create($validated);

        // Asociar las marcas seleccionadas
        if ($request->has('brands')) {
            $category->brands()->attach($request->brands);
        }

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Mostrar una categoría específica
     */
    public function show(Category $category)
    {
        $category->load('brands');
        return view('categories.show', compact('category'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Category $category)
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        return view('categories.edit', compact('category', 'brands'));
    }

    /**
     * Actualizar categoría
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'module_type' => 'required|in:peluqueria,distribuidora',
            'is_active' => 'boolean',
            'brands' => 'nullable|array',
            'brands.*' => 'exists:brands,id'
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        // Sincronizar las marcas
        $category->brands()->sync($request->brands ?? []);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Eliminar categoría
     */
    public function destroy(Category $category)
    {
        // Verificar si hay productos asociados
        if ($category->products()->exists()) {
            return back()->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
        }

        // Desasociar todas las marcas antes de eliminar
        $category->brands()->detach();
        $category->forceDelete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }
}
