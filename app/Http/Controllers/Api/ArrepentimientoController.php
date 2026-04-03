<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Notifications\ArrepentimientoAdminNotification;
use App\Notifications\ArrepentimientoConfirmacionNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ArrepentimientoController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'order_number' => ['required', 'string', 'max:50'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = Order::where('order_number', $validated['order_number'])->first();

        if (!$order) {
            return response()->json([
                'message' => 'No se encontró un pedido con ese número. Verificá el número e intentá de nuevo.',
            ], 422);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => 'Este pedido ya fue cancelado anteriormente.',
            ], 422);
        }

        $daysSinceOrder = $order->created_at->diffInDays(now());
        if ($daysSinceOrder > 10) {
            return response()->json([
                'message' => 'El plazo de 10 días corridos para ejercer el derecho de arrepentimiento ha vencido.',
            ], 422);
        }

        $code = $this->generateCode();

        $order->update(['status' => 'cancelled']);

        $order->load(['user', 'items']);

        $notifiable = $order->user;
        $notifiable->notify(new ArrepentimientoConfirmacionNotification($order, $code, $validated['reason'] ?? null));

        $admins = User::where('role', '!=', 'customer')->get();
        Notification::send($admins, new ArrepentimientoAdminNotification(
            $order,
            $validated['name'],
            $validated['email'],
            $code,
            $validated['reason'] ?? null,
        ));

        return response()->json([
            'message' => 'Tu solicitud de arrepentimiento fue registrada correctamente. El pedido ha sido cancelado.',
            'code' => $code,
        ]);
    }

    private function generateCode(): string
    {
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "ARR-{$date}-{$random}";
    }
}
