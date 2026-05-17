<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelAbandonedOrders extends Command
{
    protected $signature = 'orders:cancel-abandoned {--hours=72}';

    protected $description = 'Cancela pedidos pendientes de pago después de N horas y devuelve el stock';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $orders = Order::where('payment_status', 'pending')
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No hay pedidos abandonados para cancelar.');
            return 0;
        }

        foreach ($orders as $order) {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
            ]);
            $order->restoreStock();

            Log::info('Pedido abandonado cancelado', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at->toDateTimeString(),
            ]);
        }

        $this->info("Se cancelaron {$orders->count()} pedido(s) abandonado(s).");

        return 0;
    }
}
