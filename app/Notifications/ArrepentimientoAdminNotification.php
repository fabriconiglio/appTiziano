<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArrepentimientoAdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Order $order,
        private string $requesterName,
        private string $requesterEmail,
        private string $code,
        private ?string $reason = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $appUrl = env('APP_URL', 'http://localhost:8000');

        $itemList = $this->order->items->map(
            fn ($item) => "• {$item->product_name} x{$item->quantity} — $" . number_format($item->subtotal, 0, ',', '.')
        )->implode("\n");

        $mail = (new MailMessage)
            ->subject("Arrepentimiento — Pedido #{$this->order->order_number}")
            ->greeting('Solicitud de arrepentimiento recibida')
            ->line("**Código de gestión:** {$this->code}")
            ->line("**Pedido:** #{$this->order->order_number}")
            ->line("**Cliente registrado:** {$this->order->user->name} ({$this->order->user->email})")
            ->line("**Solicitante:** {$this->requesterName} ({$this->requesterEmail})")
            ->line("**Total:** $" . number_format($this->order->total, 0, ',', '.'))
            ->line('')
            ->line("**Productos:**")
            ->line($itemList);

        if ($this->reason) {
            $mail->line('')->line("**Motivo:** {$this->reason}");
        }

        $mail->line('')->line('El pedido fue cancelado automáticamente.')
             ->action('Ver pedido en el panel', $appUrl . '/orders/' . $this->order->id)
             ->salutation('Sistema de notificaciones — Tiziano Peluquería');

        return $mail;
    }
}
