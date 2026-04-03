<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

        return (new MailMessage)
            ->subject('¡Bienvenido/a a Tiziano Peluquería!')
            ->greeting("¡Hola {$notifiable->name}!")
            ->line('Gracias por registrarte en nuestra tienda online. Ya podés explorar nuestro catálogo de productos profesionales para el cuidado del cabello.')
            ->line('Encontrá shampoos, acondicionadores, tratamientos, coloración y mucho más de las mejores marcas.')
            ->action('Visitar la tienda', $frontendUrl . '/productos')
            ->line('Si tenés alguna consulta, no dudes en contactarnos.')
            ->salutation("Saludos,\nTiziano Peluquería & Spa");
    }
}
