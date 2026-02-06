<?php

namespace App\Http\Controllers;

use App\Models\DistributorClienteNoFrecuente;
use App\Models\Product;
use App\Models\SupplierInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DistributorClienteNoFrecuenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DistributorClienteNoFrecuente::with('user')->latest();

        // Filtro por búsqueda
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('distribuidor', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('productos', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('telefono', 'LIKE', "%{$searchTerm}%");
            });
        }

        $clientes = $query->paginate(15);

        return view('distributor-cliente-no-frecuentes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        return view('distributor-cliente-no-frecuentes.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'fecha' => 'required|date|before_or_equal:today',
            'monto' => 'required|numeric|min:0',
            'forma_pago' => 'required|in:efectivo,tarjeta,transferencia,deudor',
            'productos' => 'nullable|string',
            'products_purchased' => 'nullable|array',
            'products_purchased.*.product_id' => 'required_with:products_purchased|exists:supplier_inventories,id',
            'products_purchased.*.quantity' => 'required_with:products_purchased|integer|min:1',
            'products_purchased.*.price' => 'required_with:products_purchased|numeric|min:0',
            'purchase_type' => 'required|string|in:al_por_mayor,al_por_menor',
            'products_purchased.*.original_price' => 'nullable|numeric|min:0',
            'products_purchased.*.discount_type' => 'nullable|string|in:percentage,fixed',
            'products_purchased.*.discount_value' => 'nullable|numeric|min:0',
            'products_purchased.*.discount_reason' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ], [
            'fecha.required' => 'La fecha es requerida',
            'fecha.before_or_equal' => 'La fecha no puede ser futura',
            'monto.required' => 'El valor de la venta es requerido',
            'monto.min' => 'El valor de la venta debe ser mayor a 0',
            'forma_pago.required' => 'La forma de pago es requerida',
            'forma_pago.in' => 'La forma de pago seleccionada no es válida',
        ]);

        $validated['user_id'] = Auth::id();

        // Recalcular el monto desde los productos para asegurar que sea correcto
        if (!empty($validated['products_purchased'])) {
            $calculatedTotal = 0;
            foreach ($validated['products_purchased'] as $productData) {
                $quantity = isset($productData['quantity']) ? (float) $productData['quantity'] : 0;
                $price = isset($productData['price']) ? (float) $productData['price'] : 0;
                $calculatedTotal += $quantity * $price;
            }
            // Solo actualizar el monto si el cálculo es mayor a 0
            if ($calculatedTotal > 0) {
                $validated['monto'] = round($calculatedTotal, 2);
            }
        }

        // Iniciar transacción para manejar stock
        DB::beginTransaction();
        
        try {
            // Crear el cliente no frecuente
            $clienteNoFrecuente = DistributorClienteNoFrecuente::create($validated);

            // Procesar productos comprados y actualizar stock
            if (!empty($validated['products_purchased'])) {
                foreach ($validated['products_purchased'] as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    
                    if ($supplierInventory) {
                        // Verificar stock disponible
                        if ($supplierInventory->stock_quantity < $productData['quantity']) {
                            throw new \Exception("Stock insuficiente para el producto: {$supplierInventory->product_name}. Stock disponible: {$supplierInventory->stock_quantity}");
                        }
                        
                        // Descontar stock
                        $supplierInventory->decrement('stock_quantity', $productData['quantity']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('distributor-cliente-no-frecuentes.index')
                ->with('success', 'Cliente no frecuente registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al registrar el cliente no frecuente: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DistributorClienteNoFrecuente $distributorClienteNoFrecuente)
    {
        $distributorClienteNoFrecuente->load('user');
        return view('distributor-cliente-no-frecuentes.show', compact('distributorClienteNoFrecuente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistributorClienteNoFrecuente $distributorClienteNoFrecuente)
    {
        $products = Product::all();
        $supplierInventories = SupplierInventory::with('distributorBrand')->get();
        return view('distributor-cliente-no-frecuentes.edit', compact('distributorClienteNoFrecuente', 'products', 'supplierInventories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistributorClienteNoFrecuente $distributorClienteNoFrecuente)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'fecha' => 'required|date|before_or_equal:today',
            'monto' => 'required|numeric|min:0',
            'forma_pago' => 'required|in:efectivo,tarjeta,transferencia,deudor',
            'productos' => 'nullable|string',
            'products_purchased' => 'nullable|array',
            'products_purchased.*.product_id' => 'required_with:products_purchased|exists:supplier_inventories,id',
            'products_purchased.*.quantity' => 'required_with:products_purchased|integer|min:1',
            'products_purchased.*.price' => 'required_with:products_purchased|numeric|min:0',
            'purchase_type' => 'required|string|in:al_por_mayor,al_por_menor',
            'products_purchased.*.original_price' => 'nullable|numeric|min:0',
            'products_purchased.*.discount_type' => 'nullable|string|in:percentage,fixed',
            'products_purchased.*.discount_value' => 'nullable|numeric|min:0',
            'products_purchased.*.discount_reason' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
        ], [
            'fecha.required' => 'La fecha es requerida',
            'fecha.before_or_equal' => 'La fecha no puede ser futura',
            'monto.required' => 'El valor de la venta es requerido',
            'monto.min' => 'El valor de la venta debe ser mayor a 0',
            'forma_pago.required' => 'La forma de pago es requerida',
            'forma_pago.in' => 'La forma de pago seleccionada no es válida',
        ]);

        // Recalcular el monto desde los productos para asegurar que sea correcto
        if (!empty($validated['products_purchased'])) {
            $calculatedTotal = 0;
            foreach ($validated['products_purchased'] as $productData) {
                $quantity = isset($productData['quantity']) ? (float) $productData['quantity'] : 0;
                $price = isset($productData['price']) ? (float) $productData['price'] : 0;
                $calculatedTotal += $quantity * $price;
            }
            // Solo actualizar el monto si el cálculo es mayor a 0
            if ($calculatedTotal > 0) {
                $validated['monto'] = round($calculatedTotal, 2);
            }
        }

        // Iniciar transacción para manejar stock
        DB::beginTransaction();
        
        try {
            // Restaurar stock anterior si existía
            if (!empty($distributorClienteNoFrecuente->products_purchased)) {
                foreach ($distributorClienteNoFrecuente->products_purchased as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    if ($supplierInventory) {
                        $supplierInventory->increment('stock_quantity', $productData['quantity']);
                    }
                }
            }

            // Actualizar el cliente no frecuente
            $distributorClienteNoFrecuente->update($validated);

            // Procesar nuevos productos comprados y actualizar stock
            if (!empty($validated['products_purchased'])) {
                foreach ($validated['products_purchased'] as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    
                    if ($supplierInventory) {
                        // Verificar stock disponible
                        if ($supplierInventory->stock_quantity < $productData['quantity']) {
                            throw new \Exception("Stock insuficiente para el producto: {$supplierInventory->product_name}. Stock disponible: {$supplierInventory->stock_quantity}");
                        }
                        
                        // Descontar stock
                        $supplierInventory->decrement('stock_quantity', $productData['quantity']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('distributor-cliente-no-frecuentes.index')
                ->with('success', 'Cliente no frecuente actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al actualizar el cliente no frecuente: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistributorClienteNoFrecuente $distributorClienteNoFrecuente)
    {
        // Restaurar stock al eliminar
        DB::beginTransaction();
        
        try {
            if (!empty($distributorClienteNoFrecuente->products_purchased)) {
                foreach ($distributorClienteNoFrecuente->products_purchased as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    if ($supplierInventory) {
                        $supplierInventory->increment('stock_quantity', $productData['quantity']);
                    }
                }
            }

            $distributorClienteNoFrecuente->delete();
            DB::commit();

            return redirect()->route('distributor-cliente-no-frecuentes.index')
                ->with('success', 'Cliente no frecuente eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error al eliminar el cliente no frecuente: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate PDF remito for the non-frequent client
     */
    public function generateRemito(DistributorClienteNoFrecuente $distributorClienteNoFrecuente)
    {
        $distributorClienteNoFrecuente->load('user');
        
        // Procesar productos comprados
        $products = [];
        if (!empty($distributorClienteNoFrecuente->products_purchased)) {
            foreach ($distributorClienteNoFrecuente->products_purchased as $productData) {
                $supplierInventory = SupplierInventory::with('distributorBrand')->find($productData['product_id']);
                if ($supplierInventory) {
                    $products[] = [
                        'product_name' => $supplierInventory->product_name,
                        'description' => $supplierInventory->description,
                        'brand' => $supplierInventory->distributorBrand ? $supplierInventory->distributorBrand->name : $supplierInventory->brand,
                        'quantity' => $productData['quantity'],
                        'price' => $productData['price'],
                        'original_price' => $productData['original_price'] ?? $productData['price'],
                        'discount_type' => $productData['discount_type'] ?? null,
                        'discount_value' => $productData['discount_value'] ?? null,
                        'discount_reason' => $productData['discount_reason'] ?? null,
                        'subtotal' => $productData['quantity'] * $productData['price']
                    ];
                }
            }
        }

        $data = [
            'cliente' => $distributorClienteNoFrecuente,
            'products' => $products,
            'generatedDate' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('distributor-cliente-no-frecuentes.remito', $data);
        
        return $pdf->download('remito_cliente_no_frecuente_' . $distributorClienteNoFrecuente->id . '_' . date('Y-m-d_H-i-s') . '.pdf');
    }
}
