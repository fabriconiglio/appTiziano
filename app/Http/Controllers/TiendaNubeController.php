<?php

namespace App\Http\Controllers;

use App\Models\SupplierInventory;
use App\Services\TiendaNubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TiendaNubeController extends Controller
{
    private TiendaNubeService $tiendaNubeService;

    public function __construct(TiendaNubeService $tiendaNubeService)
    {
        $this->tiendaNubeService = $tiendaNubeService;
    }

    /**
     * Panel principal de Tienda Nube
     */
    public function index()
    {
        $isConfigured = $this->tiendaNubeService->isConfigured();
        $stats = $this->tiendaNubeService->getStats();
        
        // Obtener productos pendientes de sincronización
        $pendingProducts = SupplierInventory::pendingTiendaNubeSync()
            ->with(['distributorCategory', 'distributorBrand'])
            ->limit(20)
            ->get();
        
        // Obtener últimos productos sincronizados
        $recentlySynced = SupplierInventory::forTiendaNube()
            ->whereNotNull('tiendanube_synced_at')
            ->orderBy('tiendanube_synced_at', 'desc')
            ->with(['distributorCategory', 'distributorBrand'])
            ->limit(10)
            ->get();
        
        return view('tiendanube.index', compact(
            'isConfigured',
            'stats',
            'pendingProducts',
            'recentlySynced'
        ));
    }

    /**
     * Sincronizar todos los productos marcados
     */
    public function syncAll()
    {
        try {
            if (!$this->tiendaNubeService->isConfigured()) {
                return redirect()->route('tiendanube.index')
                    ->with('error', 'Las credenciales de Tienda Nube no están configuradas.');
            }

            $results = $this->tiendaNubeService->syncAllProducts();
            
            $message = "Sincronización completada: {$results['success']} exitosos, {$results['failed']} fallidos de {$results['total']} productos.";
            
            if ($results['failed'] > 0) {
                Log::warning('Errores en sincronización masiva con Tienda Nube', $results['errors']);
                return redirect()->route('tiendanube.index')
                    ->with('warning', $message);
            }
            
            return redirect()->route('tiendanube.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error en sincronización masiva con Tienda Nube: ' . $e->getMessage());
            return redirect()->route('tiendanube.index')
                ->with('error', 'Error durante la sincronización: ' . $e->getMessage());
        }
    }

    /**
     * Sincronizar un producto específico
     */
    public function sync(SupplierInventory $supplierInventory)
    {
        try {
            if (!$this->tiendaNubeService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Las credenciales de Tienda Nube no están configuradas.'
                ], 400);
            }

            $result = $this->tiendaNubeService->syncProduct($supplierInventory);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto sincronizado exitosamente con Tienda Nube.',
                    'tiendanube_product_id' => $supplierInventory->fresh()->tiendanube_product_id,
                    'tiendanube_synced_at' => $supplierInventory->fresh()->tiendanube_synced_at->format('d/m/Y H:i')
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Error desconocido al sincronizar'
            ], 400);
        } catch (\Exception $e) {
            Log::error("Error sincronizando producto {$supplierInventory->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Página de configuración
     */
    public function config()
    {
        $isConfigured = $this->tiendaNubeService->isConfigured();
        $config = [
            'has_access_token' => !empty(config('tiendanube.access_token')),
            'has_store_id' => !empty(config('tiendanube.store_id')),
            'api_url' => config('tiendanube.api_url'),
            'sync_interval' => config('tiendanube.sync.interval_hours'),
            'batch_size' => config('tiendanube.sync.batch_size'),
        ];
        
        return view('tiendanube.config', compact('isConfigured', 'config'));
    }

    /**
     * Probar conexión con Tienda Nube
     */
    public function testConnection()
    {
        try {
            $result = $this->tiendaNubeService->testConnection();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexión exitosa con Tienda Nube',
                    'store_info' => $result['data'] ?? null
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'No se pudo conectar con Tienda Nube'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar webhook para órdenes completadas
     */
    public function registerWebhook()
    {
        try {
            $webhookUrl = route('webhooks.tiendanube.order-completed');
            
            $result = $this->tiendaNubeService->registerWebhook('order/completed', $webhookUrl);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook registrado exitosamente',
                    'webhook_url' => $webhookUrl
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Error al registrar webhook'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
