<?php

namespace App\Console\Commands;

use App\Services\TiendaNubeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncTiendaNube extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiendanube:sync 
                            {--product= : ID del producto específico a sincronizar}
                            {--force : Forzar sincronización incluso de productos ya sincronizados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar productos con Tienda Nube';

    private TiendaNubeService $tiendaNubeService;

    public function __construct(TiendaNubeService $tiendaNubeService)
    {
        parent::__construct();
        $this->tiendaNubeService = $tiendaNubeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronización con Tienda Nube...');
        
        if (!$this->tiendaNubeService->isConfigured()) {
            $this->error('Las credenciales de Tienda Nube no están configuradas.');
            $this->info('Configure las siguientes variables en .env:');
            $this->info('  TIENDANUBE_ACCESS_TOKEN=tu_access_token');
            $this->info('  TIENDANUBE_STORE_ID=tu_store_id');
            return 1;
        }

        // Sincronizar un producto específico
        $productId = $this->option('product');
        if ($productId) {
            return $this->syncSingleProduct($productId);
        }

        // Sincronizar todos los productos
        return $this->syncAllProducts();
    }

    /**
     * Sincronizar un producto específico
     */
    private function syncSingleProduct($productId): int
    {
        $this->info("Sincronizando producto ID: {$productId}");
        
        $inventory = \App\Models\SupplierInventory::find($productId);
        
        if (!$inventory) {
            $this->error("Producto con ID {$productId} no encontrado.");
            return 1;
        }

        if (!$inventory->publicar_tiendanube) {
            $this->warn("El producto '{$inventory->product_name}' no está marcado para publicar en Tienda Nube.");
            return 1;
        }

        $result = $this->tiendaNubeService->syncProduct($inventory);
        
        if ($result['success']) {
            $this->info("✓ Producto sincronizado exitosamente.");
            $this->info("  - Tienda Nube ID: {$inventory->fresh()->tiendanube_product_id}");
            return 0;
        }

        $this->error("✗ Error sincronizando producto: " . ($result['error'] ?? 'Error desconocido'));
        return 1;
    }

    /**
     * Sincronizar todos los productos
     */
    private function syncAllProducts(): int
    {
        // Mostrar estadísticas previas
        $stats = $this->tiendaNubeService->getStats();
        
        $this->info('Estadísticas actuales:');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total para publicar', $stats['total_para_publicar']],
                ['Ya sincronizados', $stats['sincronizados']],
                ['Pendientes de sincronización', $stats['pendientes']],
            ]
        );

        if ($stats['total_para_publicar'] === 0) {
            $this->warn('No hay productos marcados para publicar en Tienda Nube.');
            return 0;
        }

        $this->info('Iniciando sincronización masiva...');
        $this->newLine();

        $progressBar = $this->output->createProgressBar($stats['total_para_publicar']);
        $progressBar->start();

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $products = \App\Models\SupplierInventory::forTiendaNube()->get();

        foreach ($products as $product) {
            $result = $this->tiendaNubeService->syncProduct($product);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'product' => $product->product_name,
                    'error' => $result['error'] ?? 'Error desconocido'
                ];
            }

            $progressBar->advance();
            
            // Pequeña pausa para evitar rate limiting
            usleep(500000); // 0.5 segundos
        }

        $progressBar->finish();
        $this->newLine(2);

        // Mostrar resultados
        $this->info("Sincronización completada:");
        $this->info("  ✓ Exitosos: {$results['success']}");
        
        if ($results['failed'] > 0) {
            $this->error("  ✗ Fallidos: {$results['failed']}");
            
            $this->newLine();
            $this->warn('Errores encontrados:');
            foreach ($results['errors'] as $error) {
                $this->line("  - {$error['product']}: {$error['error']}");
            }
            
            Log::warning('Sincronización con Tienda Nube completada con errores', $results);
        } else {
            Log::info('Sincronización con Tienda Nube completada exitosamente', [
                'total' => $stats['total_para_publicar'],
                'success' => $results['success']
            ]);
        }

        return $results['failed'] > 0 ? 1 : 0;
    }
}
