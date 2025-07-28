<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Comando para verificar alertas de stock bajo automáticamente
Artisan::command('stock:check-cron', function () {
    $threshold = $this->option('threshold') ?? 5;
    
    $this->info("Ejecutando verificación automática de stock bajo (umbral: {$threshold})...");
    
    try {
        // Ejecutar el job de verificación de stock bajo
        \App\Jobs\CheckLowStock::dispatch($threshold);
        
        $this->info('Verificación de stock bajo programada correctamente.');
        
        // Log para auditoría
        \Illuminate\Support\Facades\Log::info("Verificación automática de stock bajo ejecutada - Umbral: {$threshold}");
        
        return 0;
    } catch (\Exception $e) {
        $this->error('Error al ejecutar la verificación de stock bajo: ' . $e->getMessage());
        \Illuminate\Support\Facades\Log::error('Error en verificación automática de stock bajo: ' . $e->getMessage());
        
        return 1;
    }
})->purpose('Verificar productos con stock bajo automáticamente')->hourly();
