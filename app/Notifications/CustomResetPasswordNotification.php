<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Restablecimiento de Contraseña'))
            ->line(__('Estás recibiendo este correo porque recibimos una solicitud de restablecimiento de contraseña para tu cuenta.'))
            ->action(__('Restablecer Contraseña'), url(config('app.url').route('password.reset', $this->token, false)))
            ->line(__('Si no solicitaste un restablecimiento de contraseña, no es necesario realizar ninguna acción.'));
    }
}
