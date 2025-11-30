<?php

namespace App\Console\Commands;

use App\Models\AfipConfiguration;
use App\Services\AfipService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAfipTaxpayerStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afip:check-taxpayer-status 
                            {--cuit= : CUIT a consultar (por defecto usa el configurado)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consultar el estado del contribuyente en AFIP y verificar condición frente al IVA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Consultando estado del contribuyente en AFIP...');
        
        $cuit = $this->option('cuit') ?: AfipConfiguration::get('afip_cuit');
        
        if (empty($cuit)) {
            $this->error('CUIT no configurado. Configúralo primero o usa --cuit');
            return Command::FAILURE;
        }
        
        $this->info("Consultando CUIT: {$cuit}");
        $this->newLine();
        
        try {
            $afipService = new AfipService();
            
            $this->info('Obteniendo información del contribuyente...');
            $result = $afipService->getTaxpayerInfo($cuit);
            
            if (!$result['success']) {
                $error = $result['error'];
                
                // Si el error es de autorización, mostrar información útil
                if (str_contains($error, 'notAuthorized') || str_contains($error, 'autorizar')) {
                    $this->warn('⚠ No se pudo consultar el padrón (servicio no autorizado).');
                    $this->newLine();
                    $this->info('=== INFORMACIÓN BASADA EN EL ERROR DE FACTURACIÓN ===');
                    $this->newLine();
                    $this->error('El error indica:');
                    $this->line('  "LA CUIT INFORMADA NO CORRESPONDE A RESPONSABLE MONOTRIBUTO"');
                    $this->newLine();
                    $this->info('=== DIAGNÓSTICO ===');
                    $this->line('El CUIT ' . $cuit . ' NO está registrado como:');
                    $this->line('  ❌ Responsable Monotributo');
                    $this->line('  ❌ Responsable Inscripto (para facturación electrónica)');
                    $this->newLine();
                    $this->info('=== ACCIONES REQUERIDAS ===');
                    $this->line('1. Ingresar a https://www.afip.gob.ar/ con tu Clave Fiscal');
                    $this->line('2. Ir a "Mi AFIP" > "Constancia de Inscripción"');
                    $this->line('3. Verificar tu condición frente al IVA:');
                    $this->line('   - Debe ser "Responsable Inscripto" o "Monotributo"');
                    $this->line('4. Si eres Monotributo, verificar que:');
                    $this->line('   - La categoría permita facturar');
                    $this->line('   - La actividad esté habilitada');
                    $this->line('   - No haya suspensiones');
                    $this->line('5. Si no eres Monotributo ni Responsable Inscripto:');
                    $this->line('   - Debes darte de alta en una de estas categorías');
                    $this->line('   - O usar otro CUIT que sí esté autorizado');
                    $this->newLine();
                    $this->warn('=== IMPORTANTE ===');
                    $this->line('Este es un problema administrativo de AFIP.');
                    $this->line('Debe resolverse desde el portal de AFIP.');
                    $this->line('Una vez resuelto, el sistema funcionará correctamente.');
                    return Command::FAILURE;
                }
                
                $this->error('Error al obtener información: ' . $error);
                return Command::FAILURE;
            }
            
            $data = $result['data'];
            
            $this->info('✅ Información obtenida exitosamente');
            $this->newLine();
            
            // Mostrar información relevante
            $this->line('=== INFORMACIÓN DEL CONTRIBUYENTE ===');
            $this->newLine();
            
            if (isset($data->denominacion)) {
                $this->line("Razón Social: " . $data->denominacion);
            }
            
            if (isset($data->domicilioFiscal)) {
                $domicilio = $data->domicilioFiscal;
                $this->line("Domicilio Fiscal:");
                if (isset($domicilio->direccion)) {
                    $this->line("  Dirección: " . $domicilio->direccion);
                }
                if (isset($domicilio->localidad)) {
                    $this->line("  Localidad: " . $domicilio->localidad);
                }
                if (isset($domicilio->codPostal)) {
                    $this->line("  Código Postal: " . $domicilio->codPostal);
                }
            }
            
            $this->newLine();
            $this->line('=== CONDICIÓN FRENTE AL IVA ===');
            
            if (isset($data->impuesto)) {
                foreach ($data->impuesto as $impuesto) {
                    if (isset($impuesto->idImpuesto) && $impuesto->idImpuesto == 30) { // IVA
                        $this->line("ID Impuesto: " . ($impuesto->idImpuesto ?? 'N/A'));
                        $this->line("Descripción: " . ($impuesto->descripcionImpuesto ?? 'N/A'));
                        
                        if (isset($impuesto->fechaInicio)) {
                            $this->line("Fecha Inicio: " . $impuesto->fechaInicio);
                        }
                        
                        if (isset($impuesto->fechaFin)) {
                            $this->line("Fecha Fin: " . $impuesto->fechaFin);
                        } else {
                            $this->line("Fecha Fin: Activo");
                        }
                        
                        // Verificar condición
                        $descripcion = strtoupper($impuesto->descripcionImpuesto ?? '');
                        if (str_contains($descripcion, 'MONOTRIBUTO')) {
                            $this->warn('⚠ CONDICIÓN: MONOTRIBUTO');
                            $this->info('   El contribuyente está registrado como Monotributo.');
                        } elseif (str_contains($descripcion, 'RESPONSABLE INSCRIPTO')) {
                            $this->info('✓ CONDICIÓN: RESPONSABLE INSCRIPTO');
                            $this->info('   El contribuyente está autorizado para emitir facturas.');
                        } elseif (str_contains($descripcion, 'EXENTO')) {
                            $this->warn('⚠ CONDICIÓN: EXENTO');
                        } else {
                            $this->line("Condición: " . $descripcion);
                        }
                    }
                }
            } else {
                $this->warn('No se encontró información de impuestos.');
            }
            
            $this->newLine();
            $this->line('=== ACTIVIDADES ===');
            
            if (isset($data->actividad)) {
                foreach ($data->actividad as $actividad) {
                    $this->line("Código: " . ($actividad->idActividad ?? 'N/A'));
                    $this->line("Descripción: " . ($actividad->descripcionActividad ?? 'N/A'));
                    if (isset($actividad->estado)) {
                        $estado = $actividad->estado;
                        if ($estado == 'A' || $estado == 'ACTIVO') {
                            $this->info("  Estado: ACTIVO ✓");
                        } else {
                            $this->warn("  Estado: " . $estado);
                        }
                    }
                    $this->newLine();
                }
            } else {
                $this->warn('No se encontró información de actividades.');
            }
            
            $this->newLine();
            $this->line('=== DATOS COMPLETOS (JSON) ===');
            $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $this->newLine();
            $this->info('=== RECOMENDACIONES ===');
            
            // Verificar si puede emitir facturas
            $puedeEmitir = false;
            if (isset($data->impuesto)) {
                foreach ($data->impuesto as $impuesto) {
                    if (isset($impuesto->idImpuesto) && $impuesto->idImpuesto == 30) {
                        $descripcion = strtoupper($impuesto->descripcionImpuesto ?? '');
                        if (str_contains($descripcion, 'RESPONSABLE INSCRIPTO') || 
                            str_contains($descripcion, 'MONOTRIBUTO')) {
                            $puedeEmitir = true;
                            break;
                        }
                    }
                }
            }
            
            if (!$puedeEmitir) {
                $this->error('❌ El contribuyente NO está autorizado para emitir facturas electrónicas.');
                $this->warn('   Debe estar registrado como:');
                $this->warn('   - Responsable Inscripto, o');
                $this->warn('   - Monotributo con actividad habilitada');
                $this->newLine();
                $this->info('   Acciones a realizar:');
                $this->line('   1. Ingresar a https://www.afip.gob.ar/');
                $this->line('   2. Verificar o actualizar la condición frente al IVA');
                $this->line('   3. Habilitar facturación electrónica si corresponde');
            } else {
                $this->info('✓ El contribuyente está autorizado para emitir facturas.');
                $this->warn('   Si aún así recibes errores, verifica:');
                $this->line('   - Que el punto de venta esté habilitado');
                $this->line('   - Que la actividad permita facturar');
                $this->line('   - Que no haya suspensiones o restricciones');
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error al consultar información: ' . $e->getMessage());
            Log::error('Error consultando estado del contribuyente', [
                'cuit' => $cuit,
                'error' => $e->getMessage()
            ]);
            return Command::FAILURE;
        }
    }
}

