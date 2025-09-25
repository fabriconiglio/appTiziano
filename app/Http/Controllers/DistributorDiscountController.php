<?php

namespace App\Http\Controllers;

use App\Models\DistributorDiscount;
use App\Models\DistributorClient;
use App\Models\SupplierInventory;
use Illuminate\Http\Request;

class DistributorDiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DistributorDiscount::with(['distributorClient', 'supplierInventory']);

        // Filtro por distribuidor
        if ($request->has('distributor_client_id') && $request->distributor_client_id) {
            $query->where('distributor_client_id', $request->distributor_client_id);
        }

        // Filtro por tipo de descuento
        if ($request->has('discount_type') && $request->discount_type) {
            $query->where('discount_type', $request->discount_type);
        }

        // Filtro por estado
        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'valid':
                    $query->valid();
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        // Filtro por búsqueda de texto
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('product_name', 'LIKE', "%{$search}%")
                  ->orWhere('product_sku', 'LIKE', "%{$search}%")
                  ->orWhereHas('distributorClient', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'LIKE', "%{$search}%")
                                  ->orWhere('surname', 'LIKE', "%{$search}%");
                  });
            });
        }

        $discounts = $query->latest()->paginate(15);
        
        // Obtener datos para los filtros
        $distributorClients = DistributorClient::orderBy('name')->get();
        $discountTypes = [
            'percentage' => 'Porcentaje',
            'fixed_amount' => 'Monto Fijo',
            'gift' => 'Regalo'
        ];

        return view('distributor_discounts.index', compact(
            'discounts', 
            'distributorClients', 
            'discountTypes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $distributorClients = DistributorClient::orderBy('name')->get();
        $supplierInventories = SupplierInventory::where('status', 'available')
                                               ->orderBy('product_name')
                                               ->get();
        
        return view('distributor_discounts.create', compact(
            'distributorClients', 
            'supplierInventories'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'distributor_client_ids' => 'required_unless:applies_to_all_distributors,1|array',
            'distributor_client_ids.*' => 'exists:distributor_clients,id',
            'supplier_inventory_ids' => 'nullable|array',
            'supplier_inventory_ids.*' => 'exists:supplier_inventories,id',
            'product_name' => 'nullable|string|max:255',
            'product_sku' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed_amount,gift',
            'discount_value' => 'required_unless:discount_type,gift|numeric|min:0',
            'minimum_quantity' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date|after_or_equal:today',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'boolean',
            'applies_to_all_products' => 'boolean',
            'applies_to_all_distributors' => 'boolean',
            'description' => 'required|string|max:255',
            'conditions' => 'nullable|string',
            // Ignorar productos de regalo a menos que el tipo sea "gift"
            'gift_products' => 'nullable|array|exclude_unless:discount_type,gift',
            'gift_products.*' => 'string|max:255|exclude_unless:discount_type,gift',
            'max_uses' => 'nullable|integer|min:1',
        ], [
            'distributor_client_id.required' => 'El distribuidor es requerido.',
            'discount_type.required' => 'El tipo de descuento es requerido.',
            'discount_value.required_unless' => 'El valor del descuento es requerido para descuentos de porcentaje y monto fijo.',
            'minimum_quantity.required' => 'La cantidad mínima es requerida.',
            'description.required' => 'La descripción es requerida.',
            'valid_from.after_or_equal' => 'La fecha de inicio debe ser hoy o posterior.',
            'valid_until.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ]);

        // MOD-027 (master): Agregada lógica para aplicar descuentos a todos los distribuidores
        // Si aplica a todos los distribuidores, obtener todos los IDs
        if ($validated['applies_to_all_distributors'] ?? false) {
            $validated['distributor_client_ids'] = \App\Models\DistributorClient::pluck('id')->toArray();
        }
        
        // Compatibilidad: setear distributor_client_id con el primero
        $validated['distributor_client_id'] = $validated['distributor_client_ids'][0] ?? null;

        // Procesar productos de regalo
        if ($validated['discount_type'] === 'gift' && $request->has('gift_products')) {
            $giftProducts = array_filter($request->gift_products, function($product) {
                return !empty(trim($product));
            });
            $validated['gift_products'] = array_values($giftProducts);
        }

        // Si no se especifica un producto específico (lista o manual) y tampoco aplica a todos
        if (!($validated['applies_to_all_products'] ?? false) && 
            empty($validated['supplier_inventory_ids']) && 
            empty($validated['product_name']) && 
            empty($validated['product_sku'])) {
            return back()->withErrors([
                'product_selection' => 'Debe especificar un producto específico o indicar que aplica a todos los productos.'
            ])->withInput();
        }

        // Compatibilidad: setear distributor_client_id con el primero
        $validated['distributor_client_id'] = $validated['distributor_client_ids'][0] ?? null;

        DistributorDiscount::create($validated);

        return redirect()->route('distributor-discounts.index')
                        ->with('success', 'Descuento creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DistributorDiscount $distributorDiscount)
    {
        $distributorDiscount->load(['distributorClient', 'supplierInventory']);
        
        return view('distributor_discounts.show', compact('distributorDiscount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistributorDiscount $distributorDiscount)
    {
        $distributorClients = DistributorClient::orderBy('name')->get();
        $supplierInventories = SupplierInventory::where('status', 'available')
                                               ->orderBy('product_name')
                                               ->get();
        
        return view('distributor_discounts.edit', compact(
            'distributorDiscount',
            'distributorClients', 
            'supplierInventories'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistributorDiscount $distributorDiscount)
    {
        $validated = $request->validate([
            'distributor_client_ids' => 'required_unless:applies_to_all_distributors,1|array',
            'distributor_client_ids.*' => 'exists:distributor_clients,id',
            'supplier_inventory_ids' => 'nullable|array',
            'supplier_inventory_ids.*' => 'exists:supplier_inventories,id',
            'product_name' => 'nullable|string|max:255',
            'product_sku' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed_amount,gift',
            'discount_value' => 'required_unless:discount_type,gift|numeric|min:0',
            'minimum_quantity' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'boolean',
            'applies_to_all_products' => 'boolean',
            'applies_to_all_distributors' => 'boolean',
            'description' => 'required|string|max:255',
            'conditions' => 'nullable|string',
            // Ignorar productos de regalo a menos que el tipo sea "gift"
            'gift_products' => 'nullable|array|exclude_unless:discount_type,gift',
            'gift_products.*' => 'string|max:255|exclude_unless:discount_type,gift',
            'max_uses' => 'nullable|integer|min:1',
        ], [
            'distributor_client_id.required' => 'El distribuidor es requerido.',
            'discount_type.required' => 'El tipo de descuento es requerido.',
            'discount_value.required_unless' => 'El valor del descuento es requerido para descuentos de porcentaje y monto fijo.',
            'minimum_quantity.required' => 'La cantidad mínima es requerida.',
            'description.required' => 'La descripción es requerida.',
            'valid_until.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ]);

        // MOD-027 (master): Agregada lógica para aplicar descuentos a todos los distribuidores
        // Si aplica a todos los distribuidores, obtener todos los IDs
        if ($validated['applies_to_all_distributors'] ?? false) {
            $validated['distributor_client_ids'] = \App\Models\DistributorClient::pluck('id')->toArray();
        }

        // Procesar productos de regalo
        if ($validated['discount_type'] === 'gift' && $request->has('gift_products')) {
            $giftProducts = array_filter($request->gift_products, function($product) {
                return !empty(trim($product));
            });
            $validated['gift_products'] = array_values($giftProducts);
        }

        // Si no se especifica un producto específico pero tampoco aplica a todos
        if (!($validated['applies_to_all_products'] ?? false) && 
            empty($validated['supplier_inventory_ids']) && 
            empty($validated['product_name']) && 
            empty($validated['product_sku'])) {
            return back()->withErrors([
                'product_selection' => 'Debe especificar un producto específico o indicar que aplica a todos los productos.'
            ])->withInput();
        }

        $distributorDiscount->update($validated);

        return redirect()->route('distributor-discounts.index')
                        ->with('success', 'Descuento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistributorDiscount $distributorDiscount)
    {
        $distributorDiscount->delete();

        return redirect()->route('distributor-discounts.index')
                        ->with('success', 'Descuento eliminado exitosamente.');
    }

    /**
     * Toggle the active status of a discount
     */
    public function toggleStatus(DistributorDiscount $distributorDiscount)
    {
        $distributorDiscount->update([
            'is_active' => !$distributorDiscount->is_active
        ]);

        $status = $distributorDiscount->is_active ? 'activado' : 'desactivado';
        
        return back()->with('success', "Descuento {$status} exitosamente.");
    }

    /**
     * Get available discounts for a specific distributor and product
     */
    public function getAvailableDiscounts(Request $request)
    {
        $distributorClientId = $request->input('distributor_client_id');
        $productSku = $request->input('product_sku');
        $productName = $request->input('product_name');
        $quantity = $request->input('quantity', 1);
        $unitPrice = $request->input('unit_price', 0);

        $discounts = DistributorDiscount::valid()
                                       ->forDistributor($distributorClientId)
                                       ->get()
                                       ->filter(function ($discount) use ($productSku, $productName) {
                                           return $discount->appliesTo($productSku, $productName);
                                       });

        $applicableDiscounts = [];
        
        foreach ($discounts as $discount) {
            $calculation = $discount->calculateDiscount($quantity, $unitPrice);
            
            if ($calculation['discount_amount'] > 0 || !empty($calculation['gift_products'])) {
                $applicableDiscounts[] = [
                    'id' => $discount->id,
                    'description' => $discount->description,
                    'type' => $discount->discount_type_text,
                    'calculation' => $calculation
                ];
            }
        }

        return response()->json($applicableDiscounts);
    }
}