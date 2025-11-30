<?php

/**
 * Script para actualizar los certificados AFIP del dueño
 * 
 * Ejecutar con: php update_afip_certificates.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AfipConfiguration;

try {
    echo "Actualizando configuración AFIP...\n";
    
    // Actualizar CUIT
    AfipConfiguration::set('afip_cuit', '27257574704', 'CUIT de la empresa', true);
    echo "✓ CUIT actualizado: 27257574704\n";
    
    // Actualizar rutas de certificados
    $certPath = storage_path('app/afip/certificates/wsfe_prod_75e43124c3be273f.crt');
    $keyPath = storage_path('app/afip/certificates/clave_privada_paola.key');
    
    AfipConfiguration::set('afip_certificate_path', $certPath, 'Ruta del certificado AFIP', true);
    echo "✓ Ruta del certificado actualizada: {$certPath}\n";
    
    AfipConfiguration::set('afip_private_key_path', $keyPath, 'Ruta de la clave privada AFIP', true);
    echo "✓ Ruta de la clave privada actualizada: {$keyPath}\n";
    
    // Verificar que los archivos existen
    if (!file_exists($certPath)) {
        throw new Exception("El certificado no existe en: {$certPath}");
    }
    if (!file_exists($keyPath)) {
        throw new Exception("La clave privada no existe en: {$keyPath}");
    }
    
    echo "\n✓ Verificación de archivos exitosa\n";
    
    // Verificar que el certificado es válido
    $certContent = file_get_contents($certPath);
    if (!str_starts_with($certContent, '-----BEGIN CERTIFICATE-----')) {
        throw new Exception("El certificado no es PEM válido");
    }
    
    echo "✓ Certificado válido (formato PEM)\n";
    
    // Verificar modo producción (el certificado parece ser de producción según el nombre)
    $currentProduction = AfipConfiguration::get('afip_production', 'false');
    if ($currentProduction !== 'true') {
        echo "\n⚠ ADVERTENCIA: El certificado parece ser de PRODUCCIÓN pero el modo está en TESTING.\n";
        echo "   Considera actualizar el modo producción si es necesario.\n";
    }
    
    echo "\n✅ Configuración actualizada exitosamente!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

