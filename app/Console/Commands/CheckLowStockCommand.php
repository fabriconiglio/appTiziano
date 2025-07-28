<?php

namespace App\Console\Commands;

use App\Jobs\CheckLowStock;
use Illuminate\Console\Command;

class CheckLowStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:check-low {--threshold=5 : Umbral de stock bajo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar productos con stock bajo y enviar alertas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = $this->option('threshold');
        
        $this->info("Verificando productos con stock <= {$threshold}...");
        
        // Ejecutar el job
        CheckLowStock::dispatch($threshold);
        
        $this->info('Verificación de stock bajo completada. Las alertas se han enviado.');
        
        return 0;
    }
}
