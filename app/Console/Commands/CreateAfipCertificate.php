<?php

namespace App\Console\Commands;

use App\Models\AfipConfiguration;
use Afip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateAfipCertificate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afip:create-certificate 
                            {--cuit= : CUIT (por defecto usa el configurado)}
                            {--username= : Username de AFIP}
                            {--password= : Password de AFIP}
                            {--alias= : Alias del certificado (requerido)}
                            {--force : Ejecutar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear certificado de AFIP usando la automatización de AFIP SDK';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creando certificado de AFIP...');
        
        // Obtener configuración
        $cuit = $this->option('cuit') ?: AfipConfiguration::get('afip_cuit');
        $accessToken = AfipConfiguration::get('afip_access_token');
        
        if (empty($cuit)) {
            $this->error('CUIT no configurado. Configúralo primero o usa --cuit');
            return Command::FAILURE;
        }
        
        if (empty($accessToken)) {
            $this->error('Access Token no configurado. Configúralo primero.');
            return Command::FAILURE;
        }
        
        // Solicitar datos si no se proporcionaron
        $username = $this->option('username');
        if (empty($username)) {
            $username = $this->ask('Ingresa el username de AFIP (generalmente el CUIT)', $cuit);
        }
        
        $password = $this->option('password');
        if (empty($password)) {
            $password = $this->secret('Ingresa la contraseña de AFIP');
        }
        
        $alias = $this->option('alias');
        if (empty($alias)) {
            $alias = $this->ask('Ingresa el alias del certificado (ej: afipsdk, paola, etc.)');
            if (empty($alias)) {
                $this->error('El alias es requerido.');
                return Command::FAILURE;
            }
        }
        
        $this->info("CUIT: {$cuit}");
        $this->info("Username: {$username}");
        $this->info("Alias: {$alias}");
        
        if (!$this->option('force')) {
            if (!$this->confirm('¿Continuar con la creación del certificado?')) {
                $this->info('Operación cancelada.');
                return Command::SUCCESS;
            }
        }
        
        try {
            // Inicializar Afip con solo el access_token y CUIT
            $afip = new Afip([
                'access_token' => $accessToken,
                'CUIT' => $cuit,
                'production' => true // Crear certificado de producción
            ]);
            
            $this->info('Ejecutando creación de certificado...');
            $this->warn('Este proceso puede tardar varios minutos. Por favor espera...');
            
            // Usar el método CreateCert directamente
            $response = $afip->CreateCert($username, $password, $alias);
            
            $this->newLine();
            $this->info('✅ Certificado creado exitosamente!');
            $this->newLine();
            
            // Mostrar respuesta
            $this->line('Respuesta:');
            $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // El método CreateCert retorna directamente los datos del certificado
            if (isset($response->cert) && isset($response->key)) {
                $this->newLine();
                $this->info('El certificado y la clave privada se han generado.');
                $this->warn('IMPORTANTE: Guarda estos archivos de forma segura.');
                
                // Guardar automáticamente si se usa --force, o preguntar
                $shouldSave = $this->option('force') || $this->confirm('¿Deseas guardar el certificado y la clave en el sistema?');
                
                if ($shouldSave) {
                    $certPath = storage_path('app/afip/certificates/' . $alias . '.crt');
                    $keyPath = storage_path('app/afip/certificates/' . $alias . '.key');
                    
                    // Crear directorio si no existe
                    if (!is_dir(dirname($certPath))) {
                        mkdir(dirname($certPath), 0755, true);
                    }
                    
                    file_put_contents($certPath, $response->cert);
                    file_put_contents($keyPath, $response->key);
                    
                    chmod($certPath, 0644);
                    chmod($keyPath, 0644);
                    
                    $this->info("Certificado guardado en: {$certPath}");
                    $this->info("Clave privada guardada en: {$keyPath}");
                    
                    // Actualizar configuración
                    AfipConfiguration::set('afip_certificate_path', $certPath, 'Ruta del certificado AFIP', true);
                    AfipConfiguration::set('afip_private_key_path', $keyPath, 'Ruta de la clave privada AFIP', true);
                    
                    $this->info('Configuración actualizada.');
                }
            } elseif (is_object($response) || is_array($response)) {
                // Si la respuesta tiene otra estructura, mostrarla
                $this->line('Respuesta recibida:');
                $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            
            Log::info('Certificado de AFIP creado exitosamente', [
                'cuit' => $cuit,
                'alias' => $alias,
                'response' => $response
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Throwable $error) {
            $this->newLine();
            $this->error('❌ Error al crear el certificado:');
            $this->error($error->getMessage());
            
            // Intentar mostrar más detalles del error
            if (method_exists($error, 'getPrevious') && $error->getPrevious()) {
                $this->error('Error anterior: ' . $error->getPrevious()->getMessage());
            }
            
            Log::error('Error creando certificado de AFIP', [
                'cuit' => $cuit,
                'alias' => $alias,
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}

