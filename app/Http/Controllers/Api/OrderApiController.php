<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SupplierInventory;
use App\Models\User;
use App\Notifications\NewOrderAdminNotification;
use App\Notifications\NewOrderNotification;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:mercadopago,transfer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:supplier_inventories,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:50'],
            'shipping_province' => ['required', 'string', 'max:100'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_zip' => ['required', 'string', 'max:10'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_address_2' => ['nullable', 'string', 'max:100'],
            'shipping_method' => ['required', 'in:local_pickup,cordoba,national'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $total = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = SupplierInventory::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Stock insuficiente para \"{$product->product_name}\". Disponible: {$product->stock_quantity}",
                    ], 422);
                }

                $subtotal = $item['unit_price'] * $item['quantity'];
                $total += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stock_quantity', $item['quantity']);
            }

            $shippingCost = isset($validated['shipping_cost']) ? (float) $validated['shipping_cost'] : null;
            if ($shippingCost && $validated['shipping_method'] === 'national') {
                $total += $shippingCost;
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'total' => $total,
                'shipping_cost' => $shippingCost,
                'notes' => $validated['notes'] ?? null,
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_province' => $validated['shipping_province'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_zip' => $validated['shipping_zip'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_address_2' => $validated['shipping_address_2'] ?? null,
                'shipping_method' => $validated['shipping_method'],
            ]);

            $order->items()->createMany($orderItems);

            $order->load('items');

            $request->user()->notify(new NewOrderNotification($order));

            $admins = User::where('role', '!=', 'customer')->get();
            Notification::send($admins, new NewOrderAdminNotification($order->load('user')));

            $response = ['order' => $this->formatOrder($order)];

            if ($validated['payment_method'] === 'mercadopago') {
                try {
                    $mp = app(MercadoPagoService::class);
                    $response['checkout_url'] = $mp->createPreference($order);
                } catch (\Throwable $e) {
                    Log::error('Error creando preferencia Mercado Pago', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage(),
                    ]);
                    $order->update(['payment_status' => 'failed']);
                    return response()->json([
                        'message' => 'Error al procesar el pago con Mercado Pago. Intentá de nuevo.',
                    ], 502);
                }
            }

            return response()->json($response, 201);
        });
    }

    /**
     * Webhook público de Mercado Pago.
     * MP envía notificaciones con el body { action, data: { id }, type } o
     * como querystring ?topic=payment&id=123. Sólo nos interesa el topic 'payment'.
     */
    public function mercadopagoWebhook(Request $request, MercadoPagoService $mp): JsonResponse
    {
        $type = $request->input('type') ?? $request->query('topic');
        $paymentId = $request->input('data.id') ?? $request->query('id');

        if ($type === 'payment' && $paymentId) {
            try {
                $mp->handlePaymentNotification((string) $paymentId);
            } catch (\Throwable $e) {
                Log::error('MP webhook error', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Siempre 200: MP reintenta en error.
        return response()->json(['received' => true]);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($order) => $this->formatOrder($order));

        return response()->json($orders);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->findOrFail($id);

        return response()->json($this->formatOrder($order));
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'mercadopago_preference_id' => $order->mercadopago_preference_id,
            'mercadopago_payment_id' => $order->mercadopago_payment_id,
            'total' => (float) $order->total,
            'shipping_cost' => $order->shipping_cost ? (float) $order->shipping_cost : null,
            'notes' => $order->notes,
            'shipping_name' => $order->shipping_name,
            'shipping_phone' => $order->shipping_phone,
            'shipping_province' => $order->shipping_province,
            'shipping_city' => $order->shipping_city,
            'shipping_zip' => $order->shipping_zip,
            'shipping_address' => $order->shipping_address,
            'shipping_address_2' => $order->shipping_address_2,
            'shipping_method' => $order->shipping_method,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
            ])->toArray(),
            'created_at' => $order->created_at->toISOString(),
        ];
    }
}
