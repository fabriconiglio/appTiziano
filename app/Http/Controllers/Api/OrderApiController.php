<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderAdminNotification;
use App\Notifications\NewOrderNotification;
use App\Services\TacaTacaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OrderApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:taca_taca,transfer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $total = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->current_stock < $item['quantity']) {
                    return response()->json([
                        'message' => "Stock insuficiente para \"{$product->name}\". Disponible: {$product->current_stock}",
                    ], 422);
                }

                $subtotal = $item['unit_price'] * $item['quantity'];
                $total += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ];

                $product->decrement('current_stock', $item['quantity']);
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
            ]);

            $order->items()->createMany($orderItems);

            $order->load('items');

            $request->user()->notify(new NewOrderNotification($order));

            $admins = User::where('role', '!=', 'customer')->get();
            Notification::send($admins, new NewOrderAdminNotification($order->load('user')));

            $response = ['order' => $this->formatOrder($order)];

            if ($validated['payment_method'] === 'taca_taca') {
                try {
                    $tacaTacaService = app(TacaTacaService::class);
                    $checkoutUrl = $tacaTacaService->createPaymentIntent($order);
                    $response['checkout_url'] = $checkoutUrl;
                } catch (\Exception $e) {
                    $order->update(['payment_status' => 'failed']);
                    return response()->json([
                        'message' => 'Error al procesar el pago con Taca Taca. Intentá de nuevo.',
                    ], 502);
                }
            }

            return response()->json($response, 201);
        });
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
            'total' => (float) $order->total,
            'notes' => $order->notes,
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
