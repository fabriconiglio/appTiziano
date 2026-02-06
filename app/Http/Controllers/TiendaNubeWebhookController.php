<?php

namespace App\Http\Controllers;

use App\Services\TiendaNubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TiendaNubeWebhookController extends Controller
{
    private TiendaNubeService $tiendaNubeService;

    public function __construct(TiendaNubeService $tiendaNubeService)
    {
        $this->tiendaNubeService = $tiendaNubeService;
    }

    /**
     * Procesar webhook de orden completada
     */
    public function orderCompleted(Request $request)
    {
        try {
            // Validar firma del webhook
            if (!$this->validateWebhookSignature($request)) {
                Log::warning('Webhook de Tienda Nube recibido con firma inválida');
                return response()->json(['error' => 'Firma inválida'], 401);
            }

            $payload = $request->all();
            
            Log::info('Webhook de Tienda Nube recibido - Orden completada', [
                'order_id' => $payload['id'] ?? 'N/A',
                'store_id' => $payload['store_id'] ?? 'N/A'
            ]);

            // Procesar la orden
            $result = $this->tiendaNubeService->processOrderCompleted($payload);

            if ($result['success']) {
                Log::info('Orden procesada correctamente', $result['results'] ?? []);
                return response()->json(['success' => true], 200);
            }

            Log::error('Error procesando orden de Tienda Nube', [
                'error' => $result['error'] ?? 'Error desconocido'
            ]);
            
            return response()->json(['error' => $result['error']], 500);
        } catch (\Exception $e) {
            Log::error('Excepción procesando webhook de Tienda Nube: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    /**
     * Validar la firma del webhook
     */
    private function validateWebhookSignature(Request $request): bool
    {
        $webhookSecret = config('tiendanube.webhook_secret');
        
        // Si no hay secret configurado, aceptar todos los webhooks (solo para desarrollo)
        if (empty($webhookSecret)) {
            Log::warning('Webhook secret no configurado - aceptando webhook sin validación');
            return true;
        }

        // Obtener la firma del header
        $signature = $request->header('X-Linkedstore-HMAC-SHA256');
        
        if (empty($signature)) {
            return false;
        }

        // Calcular la firma esperada
        $payload = $request->getContent();
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $webhookSecret, true));

        return hash_equals($expectedSignature, $signature);
    }
}
