<?php

namespace App\Console\Commands;

use App\Services\AfipService;
use Illuminate\Console\Command;

class GetAfipSalesPoints extends Command
{
    protected $signature = 'afip:get-sales-points';
    protected $description = 'Obtener puntos de venta disponibles desde AFIP';

    public function handle()
    {
        $this->info('Consultando puntos de venta disponibles en AFIP...');
        
        try {
            $afipService = new AfipService();
            
            // Obtener puntos de venta
            $salesPoints = $afipService->afip->ElectronicBilling->GetSalesPoints();
            
            $this->info('✅ Puntos de venta obtenidos exitosamente');
            $this->newLine();
            
            if (empty($salesPoints)) {
                $this->warn('No se encontraron puntos de venta.');
                return Command::SUCCESS;
            }
            
            $this->line('=== PUNTOS DE VENTA DISPONIBLES ===');
            $this->newLine();
            
            $tableData = [];
            foreach ($salesPoints as $point) {
                $tableData[] = [
                    'Número' => $point->Nro ?? 'N/A',
                    'Bloqueado' => isset($point->Bloqueado) ? ($point->Bloqueado ? 'Sí' : 'No') : 'N/A',
                    'FchBaja' => $point->FchBaja ?? 'Activo',
                ];
            }
            
            $this->table(
                ['Número', 'Bloqueado', 'Estado'],
                $tableData
            );
            
            $this->newLine();
            $this->info('=== DATOS COMPLETOS (JSON) ===');
            $this->line(json_encode($salesPoints, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $this->newLine();
            $this->warn('IMPORTANTE:');
            $this->line('El punto de venta debe estar habilitado para:');
            $this->line('  - Responsable Inscripto (no Monotributo)');
            $this->line('  - Facturación electrónica con web services');
            $this->line('  - No debe estar bloqueado');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error al obtener puntos de venta: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

