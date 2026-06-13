<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        // Sincronización de turnos con Google Calendar (una vía).
        'calendar_id' => env('GOOGLE_CALENDAR_ID'),
        'service_account' => env('GOOGLE_SERVICE_ACCOUNT_JSON', storage_path('app/google/service-account.json')),
        'calendar_sync_enabled' => env('GOOGLE_CALENDAR_SYNC', false),
    ],

    'mercadopago' => [
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        // Número de WhatsApp habilitado en Twilio, ej: 'whatsapp:+5493510000000'
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
        // Master switch: si está en false no se envía nada (modo dev / pre-aprobación Meta).
        'whatsapp_enabled' => env('WHATSAPP_ENABLED', false),
    ],

    'andreani' => [
        'user' => env('ANDREANI_USER'),
        'password' => env('ANDREANI_PASSWORD'),
        'cliente' => env('ANDREANI_CLIENTE'),
        'contrato' => env('ANDREANI_CONTRATO'),
        'cp_origen' => env('ANDREANI_CP_ORIGEN', '5000'),
        'debug' => env('ANDREANI_DEBUG', false),
    ],

    'frontend' => [
        'url' => env('FRONTEND_URL', 'http://localhost:3000'),
    ],

];
