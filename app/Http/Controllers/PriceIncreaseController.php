<?php

namespace App\Http\Controllers;

use App\Models\PriceIncreaseHistory;
use App\Models\SupplierInventory;
use App\Models\DistributorBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceIncreaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PriceIncreaseHistory::with(['user', 'supplierInventory', 'distributorBrand']);

        // Filtros
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('scope_type') && $request->scope_type) {
            $query->where('scope_type', $request->scope_type);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $histories = $query->latest()->paginate(10);
        $users = \App\Models\User::orderBy('name')->get();

        return view('price-increases.index', compact('histories', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = DistributorBrand::where('is_active', true)->orderBy('name')->get();
        return view('price-increases.create', compact('brands'));
    }

    /**
     * Generar vista previa del aumento sin aplicar
     */
    public function preview(Request $request)
    {
        // Preparar datos para validación condicional
        $rules = [
            'type' => 'required|in:porcentual,fijo',
            'increase_value' => 'required|numeric|min:0.01',
            'scope_type' => 'required|in:producto,marca,multiples',
            'price_types' => 'required|array',
            'price_types.*' => 'in:precio_mayor,precio_menor'
        ];

        // Agregar validaciones condicionales según el alcance
        if ($request->scope_type === 'producto') {
            $rules['supplier_inventory_id'] = 'required|exists:supplier_inventories,id';
            $rules['distributor_brand_id'] = 'nullable';
            $rules['supplier_inventory_ids'] = 'nullable';
        } elseif ($request->scope_type === 'marca') {
            $rules['distributor_brand_id'] = 'required|exists:distributor_brands,id';
            $rules['supplier_inventory_id'] = 'nullable';
            $rules['supplier_inventory_ids'] = 'nullable';
        } else {
            // multiples
            $rules['supplier_inventory_ids'] = 'required|array';
            $rules['supplier_inventory_ids.*'] = 'exists:supplier_inventories,id';
            $rules['supplier_inventory_id'] = 'nullable';
            $rules['distributor_brand_id'] = 'nullable';
        }

        $validated = $request->validate($rules);

        // Normalizar supplier_inventory_ids para multiples
        if ($validated['scope_type'] === 'multiples' && isset($validated['supplier_inventory_ids'])) {
            $validated['supplier_inventory_ids'] = is_array($validated['supplier_inventory_ids']) 
                ? $validated['supplier_inventory_ids'] 
                : [$validated['supplier_inventory_ids']];
        }

        // Validación adicional para porcentual
        if ($validated['type'] === 'porcentual' && $validated['increase_value'] > 100) {
            return back()->withErrors(['increase_value' => 'El porcentaje no puede ser mayor a 100%'])->withInput();
        }

        // Obtener productos afectados
        $products = $this->getAffectedProducts($validated);
        
        if ($products->isEmpty()) {
            return back()->withErrors(['scope_type' => 'No se encontraron productos para aplicar el aumento'])->withInput();
        }

        // Calcular nuevos precios
        $previewData = [];
        foreach ($products as $product) {
            $previousPrices = [
                'precio_mayor' => $product->precio_mayor ?? 0,
                'precio_menor' => $product->precio_menor ?? 0
            ];

            $newPrices = [
                'precio_mayor' => $previousPrices['precio_mayor'],
                'precio_menor' => $previousPrices['precio_menor']
            ];

            foreach ($validated['price_types'] as $priceType) {
                $oldPrice = $previousPrices[$priceType] ?? 0;
                if ($oldPrice > 0) {
                    $newPrice = $this->calculateNewPrice($oldPrice, $validated['type'], $validated['increase_value']);
                    $newPrices[$priceType] = round($newPrice, 2);
                }
            }

            $previewData[] = [
                'product' => $product,
                'previous_prices' => $previousPrices,
                'new_prices' => $newPrices
            ];
        }

        return view('price-increases.preview', [
            'preview_data' => $previewData,
            'form_data' => $validated
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:porcentual,fijo',
            'increase_value' => 'required|numeric|min:0.01',
            'scope_type' => 'required|in:producto,marca,multiples',
            'supplier_inventory_id' => 'nullable|exists:supplier_inventories,id',
            'distributor_brand_id' => 'nullable|exists:distributor_brands,id',
            'price_types' => 'required|array',
            'price_types.*' => 'in:precio_mayor,precio_menor',
            'products_data' => 'required|json'
        ]);

        // Validación adicional para porcentual
        if ($validated['type'] === 'porcentual' && $validated['increase_value'] > 100) {
            return back()->withErrors(['increase_value' => 'El porcentaje no puede ser mayor a 100%'])->withInput();
        }

        $productsData = json_decode($validated['products_data'], true);

        if (empty($productsData)) {
            return back()->withErrors(['products_data' => 'No hay productos para actualizar'])->withInput();
        }

        DB::beginTransaction();

        try {
            $affectedProductIds = [];
            $previousPricesData = [];
            $newPricesData = [];

            foreach ($productsData as $item) {
                $productId = $item['product_id'];
                $product = SupplierInventory::find($productId);

                if (!$product) {
                    continue;
                }

                $affectedProductIds[] = $productId;

                // Usar precios del preview (asegura consistencia)
                $previousPrices = $item['previous_prices'] ?? [
                    'precio_mayor' => $product->precio_mayor ?? 0,
                    'precio_menor' => $product->precio_menor ?? 0
                ];

                $newPrices = $item['new_prices'] ?? [];

                // Aplicar nuevos precios al producto
                foreach ($validated['price_types'] as $priceType) {
                    if (isset($newPrices[$priceType])) {
                        $product->$priceType = round($newPrices[$priceType], 2);
                    }
                }

                $previousPricesData[$productId] = $previousPrices;
                $newPricesData[$productId] = $newPrices;

                $product->save();
            }

            // Crear registro de historial
            PriceIncreaseHistory::create([
                'type' => $validated['type'],
                'increase_value' => $validated['increase_value'],
                'scope_type' => $validated['scope_type'],
                'supplier_inventory_id' => $validated['supplier_inventory_id'] ?? null,
                'distributor_brand_id' => $validated['distributor_brand_id'] ?? null,
                'user_id' => Auth::id(),
                'affected_products' => $affectedProductIds,
                'previous_prices' => $previousPricesData,
                'new_prices' => $newPricesData,
                'price_types' => $validated['price_types']
            ]);

            DB::commit();

            return redirect()
                ->route('price-increases.index')
                ->with('success', 'Aumento de precios aplicado exitosamente a ' . count($affectedProductIds) . ' producto(s).');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al aplicar aumento de precios: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Error al aplicar el aumento de precios. Por favor, intente nuevamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PriceIncreaseHistory $priceIncrease)
    {
        $priceIncrease->load(['user', 'supplierInventory', 'distributorBrand']);
        
        // Obtener productos afectados con sus datos actuales
        $affectedProductIds = $priceIncrease->affected_products ?? [];
        $products = SupplierInventory::whereIn('id', $affectedProductIds)
            ->with('distributorBrand')
            ->get()
            ->keyBy('id');

        return view('price-increases.show', compact('priceIncrease', 'products'));
    }

    /**
     * Obtener productos afectados según el alcance
     */
    private function getAffectedProducts(array $data)
    {
        if ($data['scope_type'] === 'producto') {
            return SupplierInventory::where('id', $data['supplier_inventory_id'])->get();
        } elseif ($data['scope_type'] === 'marca') {
            return SupplierInventory::where('distributor_brand_id', $data['distributor_brand_id'])->get();
        } else {
            // multiples
            $productIds = $data['supplier_inventory_ids'] ?? [];
            return SupplierInventory::whereIn('id', $productIds)->get();
        }
    }

    /**
     * Calcular nuevo precio según tipo de aumento
     */
    private function calculateNewPrice(float $oldPrice, string $type, float $value): float
    {
        if ($type === 'porcentual') {
            return $oldPrice * (1 + $value / 100);
        } else {
            return $oldPrice + $value;
        }
    }
}

