<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Verificación de Correo Electrónico'))
            ->greeting(__('¡Hola!'))
            ->line(__('Por favor, haz clic en el botón de abajo para verificar tu dirección de correo electrónico.'))
            ->action(__('Verificar Dirección de Correo'), $this->verificationUrl($notifiable))
            ->line(__('Si no creaste una cuenta, no es necesario que hagas nada más.'))
            ->salutation(__('Saludos,') . "\n" . config('app.name'));
    }
}
