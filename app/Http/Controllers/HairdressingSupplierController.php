<?php

namespace App\Http\Controllers;

use App\Models\HairdressingSupplier;
use Illuminate\Http\Request;

class HairdressingSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = HairdressingSupplier::query();
        
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('business_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('contact_person', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('cuit', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        if ($request->has('status') && $request->get('status') !== '') {
            $query->where('is_active', $request->get('status') === 'active');
        }
        
        $suppliers = $query->orderBy('name')->paginate(15);
        
        return view('hairdressing-suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hairdressing-suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'cuit' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_time' => 'nullable|string|max:255',
            'minimum_order' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'bank_account' => 'nullable|string|max:255',
            'tax_category' => 'nullable|string|max:100'
        ]);

        HairdressingSupplier::create($validated);

        return redirect()->route('hairdressing-suppliers.index')
            ->with('success', 'Proveedor de peluquería creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HairdressingSupplier $hairdressingSupplier)
    {
        $hairdressingSupplier->load('products');
        
        $stats = [
            'total_products' => $hairdressingSupplier->products_count,
            'total_value' => $hairdressingSupplier->total_inventory_value,
            'low_stock_products' => $hairdressingSupplier->low_stock_products->count(),
            'out_of_stock_products' => $hairdressingSupplier->out_of_stock_products->count(),
        ];

        return view('hairdressing-suppliers.show', compact('hairdressingSupplier', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HairdressingSupplier $hairdressingSupplier)
    {
        return view('hairdressing-suppliers.edit', compact('hairdressingSupplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HairdressingSupplier $hairdressingSupplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'cuit' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_time' => 'nullable|string|max:255',
            'minimum_order' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'bank_account' => 'nullable|string|max:255',
            'tax_category' => 'nullable|string|max:100'
        ]);

        $hairdressingSupplier->update($validated);

        return redirect()->route('hairdressing-suppliers.index')
            ->with('success', 'Proveedor de peluquería actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HairdressingSupplier $hairdressingSupplier)
    {
        $hairdressingSupplier->delete();

        return redirect()->route('hairdressing-suppliers.index')
            ->with('success', 'Proveedor de peluquería eliminado exitosamente.');
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(HairdressingSupplier $hairdressingSupplier)
    {
        $hairdressingSupplier->update(['is_active' => !$hairdressingSupplier->is_active]);

        $status = $hairdressingSupplier->is_active ? 'activado' : 'desactivado';
        return redirect()->back()->with('success', "Proveedor de peluquería {$status} exitosamente.");
    }

    /**
     * Restore deleted supplier
     */
    public function restore($id)
    {
        $supplier = HairdressingSupplier::withTrashed()->findOrFail($id);
        $supplier->restore();

        return redirect()->back()->with('success', 'Proveedor de peluquería restaurado exitosamente.');
    }
}
