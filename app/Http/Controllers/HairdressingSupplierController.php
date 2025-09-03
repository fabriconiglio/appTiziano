<?php

namespace App\Http\Controllers;

use App\Models\HairdressingSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HairdressingSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener todos los proveedores
        $query = HairdressingSupplier::orderBy('name');

        // Aplicar filtro de búsqueda si se proporciona
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

        $suppliers = $query->paginate(15);

        // Proveedores desactivados para mostrar en la vista
        $inactiveSuppliers = HairdressingSupplier::where('is_active', false)->get();

        return view('hairdressing-suppliers.index', compact('suppliers', 'inactiveSuppliers'));
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
        $hairdressingSupplier->load(['products', 'hairdressingSupplierPurchases']);
        
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
        return back()->with('success', "Proveedor de peluquería {$status} exitosamente.");
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

    /**
     * Mostrar formulario para crear una nueva compra
     */
    public function createPurchase(HairdressingSupplier $hairdressingSupplier)
    {
        return view('hairdressing-suppliers.create-purchase', compact('hairdressingSupplier'));
    }

    /**
     * Almacenar una nueva compra del proveedor de peluquería
     */
    public function storePurchase(Request $request, HairdressingSupplier $hairdressingSupplier)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'receipt_number' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'payment_amount' => 'required|numeric|min:0',
            'balance_amount' => 'nullable|numeric|min:0',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'notes' => 'nullable|string'
        ]);

        // Procesar el archivo de la boleta si se proporciona uno
        if ($request->hasFile('receipt_file')) {
            $filePath = $request->file('receipt_file')->store('hairdressing-supplier-receipts', 'public');
            $validated['receipt_file'] = $filePath;
        } else {
            $validated['receipt_file'] = null;
        }

        // Calcular el saldo pendiente
        $validated['balance_amount'] = $validated['total_amount'] - $validated['payment_amount'];
        
        // Agregar el ID del proveedor y usuario
        $validated['hairdressing_supplier_id'] = $hairdressingSupplier->id;
        $validated['user_id'] = auth()->id();

        // Crear la compra en la base de datos
        \App\Models\HairdressingSupplierPurchase::create($validated);
        
        return redirect()->route('hairdressing-suppliers.show', $hairdressingSupplier)
            ->with('success', 'Compra registrada exitosamente.');
    }

    /**
     * Mostrar formulario para editar una compra
     */
    public function editPurchase(HairdressingSupplier $hairdressingSupplier, $purchase)
    {
        $purchase = \App\Models\HairdressingSupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->hairdressing_supplier_id !== $hairdressingSupplier->id) {
            abort(404);
        }
        
        return view('hairdressing-suppliers.edit-purchase', compact('hairdressingSupplier', 'purchase'));
    }

    /**
     * Actualizar una compra existente
     */
    public function updatePurchase(Request $request, HairdressingSupplier $hairdressingSupplier, $purchase)
    {
        $purchase = \App\Models\HairdressingSupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->hairdressing_supplier_id !== $hairdressingSupplier->id) {
            abort(404);
        }

        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'receipt_number' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'payment_amount' => 'required|numeric|min:0',
            'balance_amount' => 'nullable|numeric|min:0',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'notes' => 'nullable|string'
        ]);

        // Procesar el archivo de la boleta si se proporciona uno nuevo
        if ($request->hasFile('receipt_file')) {
            // Eliminar archivo anterior si existe
            if ($purchase->receipt_file && Storage::exists('public/' . $purchase->receipt_file)) {
                Storage::delete('public/' . $purchase->receipt_file);
            }
            
            $filePath = $request->file('receipt_file')->store('hairdressing-supplier-receipts', 'public');
            $validated['receipt_file'] = $filePath;
        } else {
            // Mantener el archivo actual
            $validated['receipt_file'] = $purchase->receipt_file;
        }

        // Calcular el saldo pendiente
        $validated['balance_amount'] = $validated['total_amount'] - $validated['payment_amount'];

        // Actualizar la compra
        $purchase->update($validated);
        
        return redirect()->route('hairdressing-suppliers.show', $hairdressingSupplier)
            ->with('success', 'Compra actualizada exitosamente.');
    }

    /**
     * Eliminar una compra del proveedor de peluquería
     */
    public function destroyPurchase(HairdressingSupplier $hairdressingSupplier, $purchase)
    {
        $purchase = \App\Models\HairdressingSupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->hairdressing_supplier_id !== $hairdressingSupplier->id) {
            abort(404);
        }

        // Eliminar el archivo de la boleta si existe
        if ($purchase->receipt_file && Storage::exists('public/' . $purchase->receipt_file)) {
            Storage::delete('public/' . $purchase->receipt_file);
        }

        // Eliminar la compra
        $purchase->delete();
        
        return redirect()->route('hairdressing-suppliers.show', $hairdressingSupplier)
            ->with('success', 'Compra eliminada exitosamente.');
    }
}
