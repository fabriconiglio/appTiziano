<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArrepentimientoConfirmacionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Order $order,
        private string $code,
        private ?string $reason = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Arrepentimiento confirmado — Pedido #{$this->order->order_number}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Tu solicitud de arrepentimiento para el pedido **#{$this->order->order_number}** fue registrada correctamente.")
            ->line("**Código de gestión:** {$this->code}")
            ->line('Guardá este código como comprobante de tu solicitud.');

        if ($this->reason) {
            $mail->line("**Motivo indicado:** {$this->reason}");
        }

        $mail->line("**Total del pedido:** $" . number_format($this->order->total, 0, ',', '.'))
             ->line('---')
             ->line('De acuerdo con el Art. 34 de la Ley 24.240 de Defensa del Consumidor, el pedido fue cancelado. Si corresponde un reembolso, nos comunicaremos con vos para coordinar la devolución.')
             ->line('Si tenés alguna consulta, contactanos a tiendatiziano@gmail.com o por WhatsApp al (351) 858-6698.')
             ->salutation("Saludos,\nTiziano Peluquería & Spa");

        return $mail;
    }
}
