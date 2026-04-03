<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    private const STATUS_LABELS = [
        'pending' => 'Pendiente',
        'confirmed' => 'Confirmado',
        'processing' => 'En proceso',
        'shipped' => 'Enviado',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado',
    ];

    private const PAYMENT_LABELS = [
        'pending' => 'Pendiente',
        'paid' => 'Pagado',
        'failed' => 'Fallido',
    ];

    public function __construct(
        private Order $order,
        private ?string $oldStatus = null,
        private ?string $oldPaymentStatus = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        $mail = (new MailMessage)
            ->subject("Actualización de tu pedido #{$this->order->order_number}")
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Tu pedido **#{$this->order->order_number}** fue actualizado:");

        if ($this->oldStatus && $this->oldStatus !== $this->order->status) {
            $from = self::STATUS_LABELS[$this->oldStatus] ?? $this->oldStatus;
            $to = self::STATUS_LABELS[$this->order->status] ?? $this->order->status;
            $mail->line("**Estado del pedido:** {$from} → **{$to}**");
        }

        if ($this->oldPaymentStatus && $this->oldPaymentStatus !== $this->order->payment_status) {
            $from = self::PAYMENT_LABELS[$this->oldPaymentStatus] ?? $this->oldPaymentStatus;
            $to = self::PAYMENT_LABELS[$this->order->payment_status] ?? $this->order->payment_status;
            $mail->line("**Estado del pago:** {$from} → **{$to}**");
        }

        if ($this->order->status === 'shipped') {
            $mail->line('¡Tu pedido ya está en camino!');
        } elseif ($this->order->status === 'delivered') {
            $mail->line('¡Tu pedido fue entregado! Esperamos que disfrutes tus productos.');
        } elseif ($this->order->status === 'cancelled') {
            $mail->line('Si tenés alguna consulta sobre la cancelación, contactanos a tizianopeluqueriaspa@gmail.com');
        }

        $mail->action('Ver mi pedido', $frontendUrl . '/checkout/confirmacion?order=' . $this->order->id)
             ->salutation("Saludos,\nTiziano Peluquería & Spa");

        return $mail;
    }
}
