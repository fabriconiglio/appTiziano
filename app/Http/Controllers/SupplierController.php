<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener todos los proveedores
        $query = Supplier::orderBy('name');

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

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('suppliers.create');
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

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['supplierInventories', 'supplierPurchases']);
        
        // Obtener estadísticas del proveedor
        $stats = [
            'total_products' => $supplier->products_count,
            'total_value' => $supplier->total_inventory_value,
            'low_stock_products' => $supplier->low_stock_products->count(),
            'out_of_stock_products' => $supplier->out_of_stock_products->count(),
        ];

        return view('suppliers.show', compact('supplier', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
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

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('suppliers.index')
                ->with('success', 'Proveedor eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'No se puede eliminar el proveedor porque tiene productos asociados.');
        }
    }

    /**
     * Restaurar un proveedor eliminado
     */
    public function restore($id)
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);
        $supplier->restore();

        return redirect()->route('suppliers.index')
            ->with('success', 'Proveedor restaurado exitosamente.');
    }

    /**
     * Cambiar el estado activo/inactivo del proveedor
     */
    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);

        $status = $supplier->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "Proveedor {$status} exitosamente.");
    }

    /**
     * Mostrar formulario para crear una nueva compra
     */
    public function createPurchase(Supplier $supplier)
    {
        return view('suppliers.create-purchase', compact('supplier'));
    }

    /**
     * Almacenar una nueva compra del proveedor
     */
    public function storePurchase(Request $request, Supplier $supplier)
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
            $filePath = $request->file('receipt_file')->store('supplier-receipts', 'public');
            $validated['receipt_file'] = $filePath;
        } else {
            $validated['receipt_file'] = null;
        }

        // Calcular el saldo pendiente
        $validated['balance_amount'] = $validated['total_amount'] - $validated['payment_amount'];
        
        // Agregar el ID del proveedor y usuario
        $validated['supplier_id'] = $supplier->id;
        $validated['user_id'] = Auth::id();

        // Crear la compra en la base de datos
        \App\Models\SupplierPurchase::create($validated);
        
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Compra registrada exitosamente.');
    }

    /**
     * Mostrar formulario para editar una compra
     */
    public function editPurchase(Supplier $supplier, $purchase)
    {
        $purchase = \App\Models\SupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->supplier_id !== $supplier->id) {
            abort(404);
        }
        
        return view('suppliers.edit-purchase', compact('supplier', 'purchase'));
    }

    /**
     * Actualizar una compra existente
     */
    public function updatePurchase(Request $request, Supplier $supplier, $purchase)
    {
        $purchase = \App\Models\SupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->supplier_id !== $supplier->id) {
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
            
            $filePath = $request->file('receipt_file')->store('supplier-receipts', 'public');
            $validated['receipt_file'] = $filePath;
        } else {
            // Mantener el archivo actual
            $validated['receipt_file'] = $purchase->receipt_file;
        }

        // Calcular el saldo pendiente
        $validated['balance_amount'] = $validated['total_amount'] - $validated['payment_amount'];

        // Actualizar la compra
        $purchase->update($validated);
        
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Compra actualizada exitosamente.');
    }

    /**
     * Eliminar una compra del proveedor
     */
    public function destroyPurchase(Supplier $supplier, $purchase)
    {
        $purchase = \App\Models\SupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->supplier_id !== $supplier->id) {
            abort(404);
        }

        // Eliminar el archivo de la boleta si existe
        if ($purchase->receipt_file && Storage::exists('public/' . $purchase->receipt_file)) {
            Storage::delete('public/' . $purchase->receipt_file);
        }

        // Eliminar la compra
        $purchase->delete();
        
        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Compra eliminada exitosamente.');
    }

    /**
     * Obtener información de una boleta por número
     * MOD-026 (master): Agregada funcionalidad de búsqueda de boletas para proveedores distribuidora
     */
    public function getReceiptTotal(Request $request, Supplier $supplier)
    {
        $receiptNumber = $request->input('receipt_number');
        
        if (!$receiptNumber) {
            return response()->json(['error' => 'Número de boleta requerido'], 400);
        }

        // Buscar la boleta más reciente que tenga saldo pendiente
        $purchase = \App\Models\SupplierPurchase::where('supplier_id', $supplier->id)
            ->where('receipt_number', $receiptNumber)
            ->where('balance_amount', '>', 0) // Solo boletas con saldo pendiente
            ->orderBy('created_at', 'desc') // La más reciente primero
            ->first();

        if ($purchase) {
            return response()->json([
                'success' => true,
                'total_amount' => $purchase->total_amount,
                'balance_amount' => $purchase->balance_amount,
                'payment_amount' => $purchase->payment_amount,
                'purchase_date' => $purchase->purchase_date->format('d/m/Y'),
                'message' => 'Boleta encontrada'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontró una boleta con ese número para este proveedor'
        ]);
    }
}
