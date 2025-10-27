<?php

namespace App\Console\Commands;

use App\Services\AfipService;
use App\Models\AfipConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestAfipConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afip:test-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnosticar la conexión y configuración de AFIP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Diagnóstico de Conexión AFIP ===');
        $this->newLine();

        // 1. Verificar configuración
        $this->info('1. Verificando configuración...');
        $config = AfipConfiguration::getAfipConfig();
        
        $this->table(
            ['Configuración', 'Valor'],
            [
                ['CUIT', $config['cuit'] ?? 'No configurado'],
                ['Ambiente', ($config['production'] ?? false) ? 'PRODUCCIÓN' : 'TESTING'],
                ['Punto de Venta', $config['point_of_sale'] ?? 'No configurado'],
                ['Tasa de IVA', $config['tax_rate'] ?? 'No configurado'],
            ]
        );
        $this->newLine();

        // 2. Verificar certificados
        $this->info('2. Verificando certificados...');
        $this->checkCertificates($config);
        $this->newLine();

        // 3. Verificar conexión con AFIP
        $this->info('3. Verificando conexión con AFIP...');
        $this->testAfipConnection($config);
        $this->newLine();

        // 4. Obtener último comprobante autorizado
        $this->info('4. Obteniendo último comprobante autorizado...');
        $this->getLastAuthorizedVoucher($config);
        $this->newLine();

        $this->info('=== Diagnóstico completado ===');
        
        return Command::SUCCESS;
    }

    /**
     * Verificar certificados
     */
    private function checkCertificates(array $config)
    {
        $certPath = $config['certificate_path'] ?? null;
        $keyPath = $config['private_key_path'] ?? null;

        // Verificar certificado
        if (!$certPath) {
            $this->error('✗ Ruta del certificado no configurada');
            return;
        }

        if (!file_exists($certPath)) {
            $this->error("✗ Certificado no encontrado: {$certPath}");
            return;
        }

        if (!is_readable($certPath)) {
            $this->error("✗ Certificado no accesible: {$certPath}");
            return;
        }

        $this->info("✓ Certificado encontrado: {$certPath}");

        // Verificar formato del certificado
        $certContent = file_get_contents($certPath);
        $certContent = trim($certContent);

        if (!str_starts_with($certContent, '-----BEGIN CERTIFICATE-----')) {
            $this->error('✗ Certificado no tiene formato PEM válido (cabecera faltante)');
            return;
        }

        if (!str_ends_with($certContent, '-----END CERTIFICATE-----')) {
            $this->error('✗ Certificado no tiene formato PEM válido (pie faltante)');
            return;
        }

        $this->info('✓ Certificado tiene formato PEM válido');

        // Parsear certificado
        $certResource = openssl_x509_read($certContent);
        if ($certResource === false) {
            $this->error('✗ No se pudo parsear el certificado');
            return;
        }

        $certInfo = openssl_x509_parse($certResource);
        if ($certInfo === false) {
            $this->error('✗ No se pudo obtener información del certificado');
            return;
        }

        $this->info('✓ Certificado parseado correctamente');

        // Mostrar información del certificado
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Emisor', $certInfo['issuer']['CN'] ?? 'N/A'],
                ['Sujeto', $certInfo['subject']['CN'] ?? 'N/A'],
                ['CUIT', $certInfo['subject']['serialNumber'] ?? 'N/A'],
                ['Válido desde', date('Y-m-d H:i:s', $certInfo['validFrom_time_t'])],
                ['Válido hasta', date('Y-m-d H:i:s', $certInfo['validTo_time_t'])],
            ]
        );

        // Verificar vencimiento
        $validTo = $certInfo['validTo_time_t'];
        if ($validTo < time()) {
            $this->error('✗ El certificado está VENCIDO');
        } else {
            $daysLeft = floor(($validTo - time()) / 86400);
            $this->info("✓ Certificado válido por {$daysLeft} días más");
        }

        // Verificar ambiente
        $issuer = $certInfo['issuer']['CN'] ?? '';
        $isProduction = $config['production'] ?? false;
        
        if ($isProduction && str_contains($issuer, 'Test')) {
            $this->error('✗ El certificado es de TESTING pero la configuración está en PRODUCCIÓN');
        } elseif (!$isProduction && !str_contains($issuer, 'Test')) {
            $this->warn('⚠ El certificado parece ser de PRODUCCIÓN pero la configuración está en TESTING');
        } else {
            $this->info('✓ El certificado corresponde al ambiente configurado');
        }

        // Verificar clave privada
        if (!$keyPath) {
            $this->error('✗ Ruta de la clave privada no configurada');
            return;
        }

        if (!file_exists($keyPath)) {
            $this->error("✗ Clave privada no encontrada: {$keyPath}");
            return;
        }

        if (!is_readable($keyPath)) {
            $this->error("✗ Clave privada no accesible: {$keyPath}");
            return;
        }

        $this->info("✓ Clave privada encontrada: {$keyPath}");
    }

    /**
     * Probar conexión con AFIP
     */
    private function testAfipConnection(array $config)
    {
        try {
            $afipService = new AfipService();
            $this->info('✓ Servicio AFIP inicializado correctamente');
            $this->info('✓ Conexión con AFIP establecida');
        } catch (\Exception $e) {
            $this->error('✗ Error al conectar con AFIP: ' . $e->getMessage());
            $this->error('  Detalles: ' . $e->getTraceAsString());
        }
    }

    /**
     * Obtener último comprobante autorizado
     */
    private function getLastAuthorizedVoucher(array $config)
    {
        try {
            $afipService = new AfipService();
            
            $pointOfSale = $config['point_of_sale'] ?? '1';
            
            // Factura A
            $lastA = $afipService->getLastAuthorizedVoucher($pointOfSale, 1);
            $this->info("Última Factura A autorizada: {$lastA}");
            
            // Factura B
            $lastB = $afipService->getLastAuthorizedVoucher($pointOfSale, 6);
            $this->info("Última Factura B autorizada: {$lastB}");
            
            // Factura C
            $lastC = $afipService->getLastAuthorizedVoucher($pointOfSale, 11);
            $this->info("Última Factura C autorizada: {$lastC}");
            
        } catch (\Exception $e) {
            $this->error('✗ Error al obtener último comprobante: ' . $e->getMessage());
        }
    }
}

