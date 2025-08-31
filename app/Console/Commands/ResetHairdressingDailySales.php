<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResetHairdressingDailySales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hairdressing-sales:reset-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resetea las estadísticas de ventas diarias de peluquería al cambiar de día';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando reseteo de estadísticas de ventas diarias de peluquería...');
        
        try {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            
            Log::info('Estadísticas de ventas diarias de peluquería reseteadas automáticamente', [
                'fecha_reseteo' => $today->format('Y-m-d H:i:s'),
                'fecha_anterior' => $yesterday->format('Y-m-d'),
                'usuario_sistema' => 'Sistema Automático',
                'modulo' => 'Peluquería'
            ]);
            
            $this->info('✅ Estadísticas de ventas diarias de peluquería reseteadas exitosamente');
            $this->info('📊 El módulo de ventas por día de peluquería mostrará datos del nuevo día');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error al resetear estadísticas de ventas diarias de peluquería: ' . $e->getMessage());
            
            Log::error('Error al resetear estadísticas de ventas diarias de peluquería', [
                'error' => $e->getMessage(),
                'fecha' => Carbon::now()->format('Y-m-d H:i:s'),
                'modulo' => 'Peluquería'
            ]);
            
            return 1;
        }
    }
} 