<?php

namespace App\Console\Commands;

use App\Jobs\CheckLowStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckStockAlertsCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-cron {--threshold=5 : Umbral de stock bajo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando de cron para verificar productos con stock bajo automáticamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = $this->option('threshold');
        
        $this->info("Ejecutando verificación automática de stock bajo (umbral: {$threshold})...");
        
        try {
            // Ejecutar el job de verificación de stock bajo
            CheckLowStock::dispatch($threshold);
            
            $this->info('Verificación de stock bajo programada correctamente.');
            
            // Log para auditoría
            Log::info("Verificación automática de stock bajo ejecutada - Umbral: {$threshold}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error al ejecutar la verificación de stock bajo: ' . $e->getMessage());
            Log::error('Error en verificación automática de stock bajo: ' . $e->getMessage());
            
            return 1;
        }
    }
}
