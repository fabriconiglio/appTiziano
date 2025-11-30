<?php

namespace App\Console\Commands;

use App\Models\AfipConfiguration;
use Afip;
use Illuminate\Console\Command;

class ListAfipCertificates extends Command
{
    protected $signature = 'afip:list-certificates';
    protected $description = 'Listar certificados disponibles en AFIP SDK';

    public function handle()
    {
        $accessToken = AfipConfiguration::get('afip_access_token');
        
        if (empty($accessToken)) {
            $this->error('Access Token no configurado.');
            return Command::FAILURE;
        }
        
        try {
            $afip = new Afip(['access_token' => $accessToken]);
            
            // Intentar obtener información de certificados
            // Nota: Esto depende de la API de AFIP SDK
            $this->info('Consultando certificados...');
            
            // Por ahora, mostrar información del certificado local
            $certPath = AfipConfiguration::get('afip_certificate_path');
            if ($certPath && file_exists($certPath)) {
                $certInfo = openssl_x509_parse(file_get_contents($certPath));
                $this->info('Certificado local encontrado:');
                $this->line('  Subject: ' . ($certInfo['subject']['CN'] ?? 'N/A'));
                $this->line('  CUIT: ' . ($certInfo['subject']['serialNumber'] ?? 'N/A'));
            }
            
            $this->warn('Para ver los certificados en AFIP SDK, visita: https://app.afipsdk.com/');
            $this->info('El alias del certificado debe coincidir con el que aparece en tu cuenta de AFIP SDK.');
            
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}

