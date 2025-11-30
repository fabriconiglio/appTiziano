<?php

namespace App\Console\Commands;

use App\Models\AfipConfiguration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateAfipCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afip:update-certificates 
                            {--cuit=27257574704 : CUIT del dueño}
                            {--certificate=wsfe_prod_75e43124c3be273f.crt : Nombre del archivo de certificado}
                            {--private-key=clave_privada_paola.key : Nombre del archivo de clave privada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar certificados AFIP del dueño en la configuración';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Actualizando configuración AFIP con certificados del dueño...');
        
        $cuit = $this->option('cuit');
        $certFileName = $this->option('certificate');
        $keyFileName = $this->option('private-key');
        
        $certPath = storage_path("app/afip/certificates/{$certFileName}");
        $keyPath = storage_path("app/afip/certificates/{$keyFileName}");
        
        // Verificar que los archivos existen
        if (!File::exists($certPath)) {
            $this->error("El certificado no existe en: {$certPath}");
            return Command::FAILURE;
        }
        
        if (!File::exists($keyPath)) {
            $this->error("La clave privada no existe en: {$keyPath}");
            return Command::FAILURE;
        }
        
        $this->info("✓ Archivos encontrados:");
        $this->line("  - Certificado: {$certPath}");
        $this->line("  - Clave privada: {$keyPath}");
        
        // Verificar formato del certificado
        $certContent = File::get($certPath);
        if (!str_starts_with($certContent, '-----BEGIN CERTIFICATE-----')) {
            $this->error("El certificado no es PEM válido");
            return Command::FAILURE;
        }
        
        $this->info("✓ Certificado válido (formato PEM)");
        
        try {
            // Actualizar CUIT
            AfipConfiguration::set('afip_cuit', $cuit, 'CUIT de la empresa', true);
            $this->info("✓ CUIT actualizado: {$cuit}");
            
            // Actualizar rutas de certificados
            AfipConfiguration::set('afip_certificate_path', $certPath, 'Ruta del certificado AFIP', true);
            $this->info("✓ Ruta del certificado actualizada");
            
            AfipConfiguration::set('afip_private_key_path', $keyPath, 'Ruta de la clave privada AFIP', true);
            $this->info("✓ Ruta de la clave privada actualizada");
            
            // Verificar modo producción
            $currentProduction = AfipConfiguration::get('afip_production', 'false');
            if ($currentProduction !== 'true' && str_contains($certFileName, 'prod')) {
                $this->warn("⚠ ADVERTENCIA: El certificado parece ser de PRODUCCIÓN pero el modo está en TESTING.");
                $this->warn("   Considera actualizar el modo producción si es necesario.");
            }
            
            $this->newLine();
            $this->info('✅ Configuración actualizada exitosamente!');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Error al actualizar configuración: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

