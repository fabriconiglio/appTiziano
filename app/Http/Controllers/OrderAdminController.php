<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items'])->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::with(['user', 'items'])->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = Order::with('user')->findOrFail($id);

        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,confirmed,processing,shipped,delivered,cancelled'],
            'payment_status' => ['nullable', 'in:pending,paid,failed'],
        ]);

        $oldStatus = $order->status;
        $oldPaymentStatus = $order->payment_status;

        if (isset($validated['status'])) {
            $order->status = $validated['status'];
        }

        if (isset($validated['payment_status'])) {
            $order->payment_status = $validated['payment_status'];
        }

        $order->save();

        $statusChanged = $oldStatus !== $order->status;
        $paymentChanged = $oldPaymentStatus !== $order->payment_status;

        if ($statusChanged || $paymentChanged) {
            $order->user->notify(new OrderStatusChangedNotification(
                $order,
                $statusChanged ? $oldStatus : null,
                $paymentChanged ? $oldPaymentStatus : null,
            ));
        }

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Estado del pedido actualizado correctamente.');
    }
}
