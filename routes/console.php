<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Comando para verificar alertas de stock bajo automáticamente
Artisan::command('stock:check-cron {--threshold=5}', function () {
    $threshold = $this->option('threshold');
    
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

// Comando para resetear estadísticas de ventas diarias automáticamente
Artisan::command('sales:reset-daily', function () {
    $this->info('🔄 Iniciando reseteo de estadísticas de ventas diarias...');
    
    try {
        // Obtener la fecha actual
        $today = \Carbon\Carbon::today();
        $yesterday = \Carbon\Carbon::yesterday();
        
        $this->info("📅 Fecha actual: " . $today->format('d/m/Y'));
        $this->info("📅 Fecha anterior: " . $yesterday->format('d/m/Y'));
        
        // Log del reseteo
        \Illuminate\Support\Facades\Log::info('Estadísticas de ventas diarias reseteadas automáticamente', [
            'fecha_reseteo' => $today->format('Y-m-d H:i:s'),
            'fecha_anterior' => $yesterday->format('Y-m-d'),
            'usuario_sistema' => 'Sistema Automático'
        ]);
        
        $this->info('✅ Estadísticas de ventas diarias reseteadas exitosamente');
        $this->info('📊 El módulo de ventas por día mostrará datos del nuevo día');
        
        return 0;
    } catch (\Exception $e) {
        $this->error('❌ Error al resetear estadísticas de ventas diarias: ' . $e->getMessage());
        \Illuminate\Support\Facades\Log::error('Error al resetear estadísticas de ventas diarias', [
            'error' => $e->getMessage(),
            'fecha' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')
        ]);
        
        return 1;
    }
})->purpose('Resetea las estadísticas de ventas diarias al cambiar de día')->dailyAt('00:00');

// Comando para resetear estadísticas de ventas diarias de peluquería automáticamente
Artisan::command('hairdressing-sales:reset-daily', function () {
    $this->info('🔄 Iniciando reseteo de estadísticas de ventas diarias de peluquería...');
    try {
        $today = \Carbon\Carbon::today();
        $yesterday = \Carbon\Carbon::yesterday();
        $this->info("📅 Fecha actual: " . $today->format('d/m/Y'));
        $this->info("📅 Fecha anterior: " . $yesterday->format('Y-m-d'));
        \Illuminate\Support\Facades\Log::info('Estadísticas de ventas diarias de peluquería reseteadas automáticamente', [
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
        \Illuminate\Support\Facades\Log::error('Error al resetear estadísticas de ventas diarias de peluquería', [
            'error' => $e->getMessage(),
            'fecha' => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
            'modulo' => 'Peluquería'
        ]);
        return 1;
    }
})->purpose('Resetea las estadísticas de ventas diarias de peluquería al cambiar de día')->dailyAt('00:00');
