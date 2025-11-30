<?php

namespace App\Console\Commands;

use App\Models\AfipConfiguration;
use Afip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AuthorizeAfipWebService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afip:authorize-web-service 
                            {--cuit= : CUIT (por defecto usa el configurado)}
                            {--username= : Username de AFIP}
                            {--password= : Password de AFIP}
                            {--alias= : Alias del certificado}
                            {--service=wsfe : Servicio web (wsfe por defecto)}
                            {--force : Ejecutar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Autorizar el uso del web service de AFIP en producción';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Autorizando Web Service de AFIP...');
        
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
            $username = $this->ask('Ingresa el username de AFIP (generalmente el CUIT)');
        }
        
        $password = $this->option('password');
        if (empty($password)) {
            $password = $this->secret('Ingresa la contraseña de AFIP');
        }
        
        $alias = $this->option('alias');
        if (empty($alias)) {
            $alias = $this->ask('Ingresa el alias del certificado', 'afipsdk');
        }
        
        $service = $this->option('service');
        
        $this->info("CUIT: {$cuit}");
        $this->info("Username: {$username}");
        $this->info("Alias: {$alias}");
        $this->info("Servicio: {$service}");
        
        if (!$this->option('force')) {
            if (!$this->confirm('¿Continuar con la autorización?')) {
                $this->info('Operación cancelada.');
                return Command::SUCCESS;
            }
        }
        
        try {
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
            
            $this->info('Ejecutando automatización auth-web-service-prod...');
            
            // Ejecutar la automatización
            $response = $afip->CreateAutomation('auth-web-service-prod', $data, true);
            
            $this->newLine();
            $this->info('✅ Autorización exitosa!');
            $this->newLine();
            
            // Mostrar respuesta
            $this->line('Respuesta:');
            $this->line(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            Log::info('Web Service de AFIP autorizado exitosamente', [
                'cuit' => $cuit,
                'service' => $service,
                'response' => $response
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Throwable $error) {
            $this->newLine();
            $this->error('❌ Error al autorizar el web service:');
            $this->error($error->getMessage());
            
            Log::error('Error autorizando Web Service de AFIP', [
                'cuit' => $cuit,
                'service' => $service,
                'error' => $error->getMessage(),
                'trace' => $error->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}

