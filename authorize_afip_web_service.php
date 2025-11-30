<?php

/**
 * Script para autorizar el Web Service de AFIP en producción
 * 
 * Ejecutar con: php authorize_afip_web_service.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AfipConfiguration;
use Afip;

try {
    echo "=== Autorización de Web Service AFIP ===\n\n";
    
    // Obtener configuración
    $cuit = AfipConfiguration::get('afip_cuit');
    $accessToken = AfipConfiguration::get('afip_access_token');
    
    if (empty($cuit)) {
        throw new Exception("CUIT no configurado. Configúralo primero.");
    }
    
    if (empty($accessToken)) {
        throw new Exception("Access Token no configurado. Configúralo primero.");
    }
    
    echo "CUIT configurado: {$cuit}\n";
    echo "Access Token: " . (empty($accessToken) ? 'NO' : 'SÍ (configurado)') . "\n\n";
    
    // Solicitar datos
    echo "Ingresa los siguientes datos:\n";
    $username = readline("Username de AFIP (generalmente el CUIT) [{$cuit}]: ");
    if (empty($username)) {
        $username = $cuit;
    }
    
    $password = readline("Contraseña de AFIP: ");
    if (empty($password)) {
        throw new Exception("La contraseña es requerida.");
    }
    
    $alias = readline("Alias del certificado [afipsdk]: ");
    if (empty($alias)) {
        $alias = 'afipsdk';
    }
    
    $service = readline("Servicio web [wsfe]: ");
    if (empty($service)) {
        $service = 'wsfe';
    }
    
    echo "\n";
    echo "Datos a usar:\n";
    echo "  CUIT: {$cuit}\n";
    echo "  Username: {$username}\n";
    echo "  Alias: {$alias}\n";
    echo "  Servicio: {$service}\n\n";
    
    $confirm = readline("¿Continuar? (s/n): ");
    if (strtolower($confirm) !== 's') {
        echo "Operación cancelada.\n";
        exit(0);
    }
    
    echo "\nInicializando AFIP SDK...\n";
    
    // Inicializar Afip con solo el access_token
    $afip = new Afip([
        'access_token' => $accessToken
    ]);
    
    // Preparar datos para la automatización
    $data = [
        'cuit' => $cuit,
        'username' => $username,
        'password' => $password,
        'alias' => $alias,
        'service' => $service
    ];
    
    echo "Ejecutando automatización auth-web-service-prod...\n";
    echo "Esto puede tomar unos minutos...\n\n";
    
    // Ejecutar la automatización
    $response = $afip->CreateAutomation('auth-web-service-prod', $data, true);
    
    echo "\n✅ Autorización exitosa!\n\n";
    echo "Respuesta:\n";
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
} catch (\Throwable $error) {
    echo "\n❌ Error:\n";
    echo $error->getMessage() . "\n";
    
    if ($error->getPrevious()) {
        echo "\nError anterior: " . $error->getPrevious()->getMessage() . "\n";
    }
    
    exit(1);
}

