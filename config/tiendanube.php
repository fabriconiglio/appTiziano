<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tienda Nube API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con la API de Tienda Nube.
    | Obtener las credenciales desde el panel de desarrolladores de Tienda Nube.
    |
    */

    'access_token' => env('TIENDANUBE_ACCESS_TOKEN', ''),

    'store_id' => env('TIENDANUBE_STORE_ID', ''),

    'webhook_secret' => env('TIENDANUBE_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | URL base de la API de Tienda Nube (Nuvemshop).
    |
    */

    'api_url' => env('TIENDANUBE_API_URL', 'https://api.tiendanube.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Sincronización
    |--------------------------------------------------------------------------
    |
    | Configuración de sincronización automática.
    |
    */

    'sync' => [
        // Horas entre sincronizaciones automáticas
        'interval_hours' => env('TIENDANUBE_SYNC_INTERVAL', 6),
        
        // Máximo de productos por lote de sincronización
        'batch_size' => env('TIENDANUBE_BATCH_SIZE', 50),
        
        // Segundos de espera entre llamadas API (rate limiting)
        'delay_between_requests' => env('TIENDANUBE_REQUEST_DELAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Imágenes
    |--------------------------------------------------------------------------
    |
    | Configuración para el manejo de imágenes de productos.
    |
    */

    'images' => [
        // Máximo de imágenes por producto
        'max_per_product' => 5,
        
        // Tamaño máximo de imagen en KB
        'max_size_kb' => 2048,
        
        // Extensiones permitidas
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        
        // Directorio de almacenamiento (relativo a storage/app/public)
        'storage_path' => 'supplier-inventories',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | Configuración de webhooks de Tienda Nube.
    |
    */

    'webhooks' => [
        // Eventos a escuchar
        'events' => [
            'order/completed',
            'order/paid',
        ],
    ],

];
