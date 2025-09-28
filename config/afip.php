<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración AFIP
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con los servicios web de AFIP
    |
    */

    'cuit' => env('AFIP_CUIT', ''),
    'production' => env('AFIP_PRODUCTION', false),
    'certificate_path' => env('AFIP_CERTIFICATE_PATH', ''),
    'private_key_path' => env('AFIP_PRIVATE_KEY_PATH', ''),
    'point_of_sale' => env('AFIP_POINT_OF_SALE', '1'),
    'tax_rate' => env('AFIP_TAX_RATE', '21.00'),

    /*
    |--------------------------------------------------------------------------
    | Tipos de Comprobantes
    |--------------------------------------------------------------------------
    |
    | Mapeo de tipos de facturas a códigos AFIP
    |
    */

    'voucher_types' => [
        'A' => 1,  // Factura A
        'B' => 6,  // Factura B
        'C' => 11, // Factura C
    ],

    /*
    |--------------------------------------------------------------------------
    | Tipos de Documentos
    |--------------------------------------------------------------------------
    |
    | Mapeo de tipos de documentos a códigos AFIP
    |
    */

    'document_types' => [
        'DNI' => 96,
        'CUIT' => 80,
        'CUIL' => 86,
        'PASAPORTE' => 94,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de IVA
    |--------------------------------------------------------------------------
    |
    | Configuración de alícuotas de IVA
    |
    */

    'tax_rates' => [
        '21' => 5,  // IVA 21%
        '10.5' => 4, // IVA 10.5%
        '0' => 3,   // IVA 0%
        'exempt' => 2, // Exento
    ],
];
