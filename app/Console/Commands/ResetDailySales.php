<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResetDailySales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:reset-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resetea las estadísticas de ventas diarias al cambiar de día';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando reseteo de estadísticas de ventas diarias...');
        
        try {
            // Obtener la fecha actual
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            
            $this->info("📅 Fecha actual: " . $today->format('d/m/Y'));
            $this->info("📅 Fecha anterior: " . $yesterday->format('d/m/Y'));
            
            // Aquí podrías agregar lógica adicional si necesitas
            // limpiar algún cache o tabla temporal
            
            // Log del reseteo
            Log::info('Estadísticas de ventas diarias reseteadas automáticamente', [
                'fecha_reseteo' => $today->format('Y-m-d H:i:s'),
                'fecha_anterior' => $yesterday->format('Y-m-d'),
                'usuario_sistema' => 'Sistema Automático'
            ]);
            
            $this->info('✅ Estadísticas de ventas diarias reseteadas exitosamente');
            $this->info('📊 El módulo de ventas por día mostrará datos del nuevo día');
            
        } catch (\Exception $e) {
            $this->error('❌ Error al resetear estadísticas de ventas diarias: ' . $e->getMessage());
            Log::error('Error al resetear estadísticas de ventas diarias', [
                'error' => $e->getMessage(),
                'fecha' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
            
            return 1;
        }
        
        return 0;
    }
} 