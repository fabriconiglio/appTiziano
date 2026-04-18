<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierInventory;
use App\Services\AndreaniService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShippingApiController extends Controller
{
    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_zip' => ['required', 'string', 'max:10'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:supplier_inventories,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $productIds = collect($validated['items'])->pluck('product_id');
        $products = SupplierInventory::whereIn('id', $productIds)->get()->keyBy('id');

        $itemsForQuote = [];
        $missingWeight = false;

        foreach ($validated['items'] as $item) {
            $product = $products->get($item['product_id']);
            if (! $product) {
                continue;
            }

            if (! $product->peso_gramos || ! $product->volumen_cm3) {
                $missingWeight = true;
                break;
            }

            $price = $product->precio_menor ?? $product->price ?? 0;

            $itemsForQuote[] = [
                'peso_gramos' => $product->peso_gramos,
                'volumen_cm3' => $product->volumen_cm3,
                'quantity' => $item['quantity'],
                'unit_price' => (float) $price,
            ];
        }

        if ($missingWeight || empty($itemsForQuote)) {
            return response()->json([
                'available' => false,
                'message' => 'Algunos productos no tienen datos de peso. Contactanos para cotizar.',
            ]);
        }

        try {
            $andreani = app(AndreaniService::class);
            $result = $andreani->cotizar($validated['shipping_zip'], $itemsForQuote);
        } catch (\Throwable $e) {
            Log::error('Shipping quote error', ['error' => $e->getMessage()]);
            return response()->json([
                'available' => false,
                'message' => 'No pudimos cotizar el envío en este momento. Contactanos para más info.',
            ]);
        }

        if (! $result) {
            return response()->json([
                'available' => false,
                'message' => 'No pudimos obtener la tarifa de Andreani. Contactanos para cotizar.',
            ]);
        }

        return response()->json([
            'available' => true,
            'carrier' => $result['carrier'],
            'cost' => $result['cost'],
            'estimated_days' => $result['estimated_days'],
        ]);
    }
}
