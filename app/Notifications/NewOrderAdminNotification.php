<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdminNotification extends Notification
{
    use Queueable;

    public function __construct(private Order $order) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $appUrl = env('APP_URL', 'http://localhost:8000');
        $paymentLabel = match ($this->order->payment_method) {
            'transfer' => 'Transferencia bancaria',
            'mercadopago' => 'Mercado Pago',
            default => ucfirst((string) $this->order->payment_method),
        };

        $itemList = $this->order->items->map(
            fn ($item) => "• {$item->product_name} x{$item->quantity} — $" . number_format($item->subtotal, 0, ',', '.')
        )->implode("\n");

        return (new MailMessage)
            ->subject("Nuevo pedido #{$this->order->order_number}")
            ->greeting('¡Nuevo pedido en la tienda!')
            ->line("**Cliente:** {$this->order->user->name} ({$this->order->user->email})")
            ->line("**Pedido:** #{$this->order->order_number}")
            ->line("**Total:** $" . number_format($this->order->total, 0, ',', '.'))
            ->line("**Método de pago:** {$paymentLabel}")
            ->line('')
            ->line("**Productos:**")
            ->line($itemList)
            ->action('Ver pedido en el panel', $appUrl . '/orders/' . $this->order->id)
            ->salutation('Sistema de notificaciones — Tiziano Peluquería');
    }
}
