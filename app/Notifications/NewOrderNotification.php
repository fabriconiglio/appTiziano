<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(private Order $order) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $paymentLabel = $this->order->payment_method === 'transfer'
            ? 'Transferencia bancaria'
            : 'Taca Taca (tarjeta)';

        $mail = (new MailMessage)
            ->subject("Pedido #{$this->order->order_number} confirmado — Tiziano Peluquería")
            ->greeting("¡Hola {$notifiable->name}!")
            ->line("Tu pedido **#{$this->order->order_number}** fue registrado correctamente.")
            ->line("**Total:** $" . number_format($this->order->total, 0, ',', '.'))
            ->line("**Método de pago:** {$paymentLabel}");

        if ($this->order->payment_method === 'transfer') {
            $whatsappText = urlencode("Hola! Realicé una transferencia para el pedido #{$this->order->order_number} por $" . number_format($this->order->total, 0, ',', '.') . ". Adjunto el comprobante.");

            $mail->line('---')
                 ->line('**Datos para la transferencia:**')
                 ->line('Banco: Banco Nación Argentina')
                 ->line('Titular: Tiziano Peluquería & Spa')
                 ->line('CBU: 0110012345678901234567')
                 ->line('Alias: TIZIANO.PELUQUERIA')
                 ->line("Referencia: {$this->order->order_number}")
                 ->line('---')
                 ->line('Una vez realizada la transferencia, envianos el comprobante:')
                 ->line("[Enviar comprobante por WhatsApp](https://wa.me/5493516197836?text={$whatsappText})")
                 ->line('O por email a tizianopeluqueriaspa@gmail.com indicando el número de pedido.');
        }

        $mail->action('Ver mi pedido', $frontendUrl . '/checkout/confirmacion?order=' . $this->order->id)
             ->salutation("Gracias por tu compra,\nTiziano Peluquería & Spa");

        return $mail;
    }
}
