<?php

namespace App\Services;

use App\Models\SupplierInventory;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TiendaNubeService
{
    private string $apiUrl;
    private string $accessToken;
    private string $storeId;
    private int $requestDelay;

    public function __construct()
    {
        $this->apiUrl = config('tiendanube.api_url');
        $this->accessToken = config('tiendanube.access_token');
        $this->storeId = config('tiendanube.store_id');
        $this->requestDelay = config('tiendanube.sync.delay_between_requests', 1);
    }

    /**
     * Verificar si el servicio está configurado correctamente
     */
    public function isConfigured(): bool
    {
        return !empty($this->accessToken) && !empty($this->storeId);
    }

    /**
     * Probar la conexión con la API de Tienda Nube
     */
    public function testConnection(): array
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'Credenciales de Tienda Nube no configuradas'
                ];
            }

            // El endpoint correcto es /store (sin s y sin ID adicional)
            $response = $this->makeRequest('GET', '/store');

            if ($response['success']) {
                return [
                    'success' => true,
                    'data' => $response['data'],
                    'message' => 'Conexión exitosa con Tienda Nube. Tienda: ' . ($response['data']['name']['es'] ?? $response['data']['name'] ?? 'Sin nombre')
                ];
            }

            return $response;
        } catch (Exception $e) {
            Log::error('Error probando conexión con Tienda Nube: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sincronizar un producto con Tienda Nube
     */
    public function syncProduct(SupplierInventory $inventory): array
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'Credenciales de Tienda Nube no configuradas'
                ];
            }

            if (!$inventory->publicar_tiendanube) {
                return [
                    'success' => false,
                    'error' => 'El producto no está marcado para publicar en Tienda Nube'
                ];
            }

            $productData = $this->prepareProductData($inventory);

            // Si ya existe en Tienda Nube, actualizar; sino, crear
            if ($inventory->tiendanube_product_id) {
                $result = $this->updateProduct($inventory->tiendanube_product_id, $productData, $inventory);
            } else {
                $result = $this->createProduct($productData, $inventory);
            }

            if ($result['success']) {
                // Actualizar registro local
                $inventory->update([
                    'tiendanube_product_id' => $result['data']['id'] ?? $inventory->tiendanube_product_id,
                    'tiendanube_variant_id' => $result['data']['variants'][0]['id'] ?? $inventory->tiendanube_variant_id,
                    'tiendanube_synced_at' => now()
                ]);

                // Sincronizar imágenes si hay
                if (!empty($inventory->images)) {
                    $this->syncImages($inventory);
                }
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Error sincronizando producto {$inventory->id} con Tienda Nube: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sincronizar todos los productos marcados para Tienda Nube
     */
    public function syncAllProducts(): array
    {
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $products = SupplierInventory::forTiendaNube()->get();
        $results['total'] = $products->count();

        foreach ($products as $product) {
            $result = $this->syncProduct($product);

            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'error' => $result['error'] ?? 'Error desconocido'
                ];
            }

            // Esperar entre requests para evitar rate limiting
            if ($this->requestDelay > 0) {
                sleep($this->requestDelay);
            }
        }

        return $results;
    }

    /**
     * Sincronizar imágenes de un producto
     * Usa base64 para subir imágenes directamente (funciona desde localhost)
     */
    public function syncImages(SupplierInventory $inventory): array
    {
        try {
            if (!$inventory->tiendanube_product_id) {
                return [
                    'success' => false,
                    'error' => 'El producto no tiene ID de Tienda Nube'
                ];
            }

            if (empty($inventory->images)) {
                return [
                    'success' => true,
                    'message' => 'No hay imágenes para sincronizar'
                ];
            }

            $uploadedCount = 0;
            $errors = [];

            foreach ($inventory->images as $index => $imagePath) {
                // Obtener la ruta completa del archivo
                $fullPath = storage_path('app/public/' . $imagePath);
                
                if (!file_exists($fullPath)) {
                    $errors[] = "Imagen no encontrada: {$imagePath}";
                    continue;
                }

                // Leer la imagen y convertir a base64
                $imageContent = file_get_contents($fullPath);
                $base64Image = base64_encode($imageContent);
                
                // Obtener el nombre del archivo para el filename
                $filename = basename($imagePath);
                
                $imageData = [
                    'attachment' => $base64Image,
                    'filename' => $filename,
                    'position' => $index + 1
                ];

                $response = $this->makeRequest(
                    'POST',
                    "/products/{$inventory->tiendanube_product_id}/images",
                    $imageData
                );

                if ($response['success']) {
                    $uploadedCount++;
                    Log::info("Imagen subida exitosamente para producto {$inventory->id}: {$filename}");
                } else {
                    $errors[] = "Error subiendo imagen {$imagePath}: " . ($response['error'] ?? 'Error desconocido');
                }

                // Pequeña pausa entre imágenes
                usleep(500000); // 0.5 segundos
            }

            return [
                'success' => empty($errors),
                'uploaded' => $uploadedCount,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            Log::error("Error sincronizando imágenes del producto {$inventory->id}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar stock de un producto en Tienda Nube
     */
    public function updateStock(SupplierInventory $inventory): array
    {
        try {
            if (!$inventory->tiendanube_product_id || !$inventory->tiendanube_variant_id) {
                return [
                    'success' => false,
                    'error' => 'El producto no tiene IDs de Tienda Nube'
                ];
            }

            $response = $this->makeRequest(
                'PUT',
                "/products/{$inventory->tiendanube_product_id}/variants/{$inventory->tiendanube_variant_id}",
                ['stock' => $inventory->stock_quantity]
            );

            if ($response['success']) {
                $inventory->update(['tiendanube_synced_at' => now()]);
            }

            return $response;
        } catch (Exception $e) {
            Log::error("Error actualizando stock del producto {$inventory->id}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar un producto de Tienda Nube
     */
    public function deleteProduct(SupplierInventory $inventory): array
    {
        try {
            if (!$inventory->tiendanube_product_id) {
                return [
                    'success' => true,
                    'message' => 'El producto no existe en Tienda Nube'
                ];
            }

            $response = $this->makeRequest(
                'DELETE',
                "/products/{$inventory->tiendanube_product_id}"
            );

            if ($response['success']) {
                $inventory->update([
                    'tiendanube_product_id' => null,
                    'tiendanube_variant_id' => null,
                    'tiendanube_synced_at' => null
                ]);
            }

            return $response;
        } catch (Exception $e) {
            Log::error("Error eliminando producto {$inventory->id} de Tienda Nube: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener categorías de Tienda Nube
     */
    public function getCategories(): array
    {
        try {
            return $this->makeRequest('GET', '/categories');
        } catch (Exception $e) {
            Log::error('Error obteniendo categorías de Tienda Nube: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Registrar webhook en Tienda Nube
     */
    public function registerWebhook(string $event, string $url): array
    {
        try {
            $response = $this->makeRequest('POST', '/webhooks', [
                'event' => $event,
                'url' => $url
            ]);

            return $response;
        } catch (Exception $e) {
            Log::error('Error registrando webhook en Tienda Nube: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Descontar stock cuando se completa una orden en Tienda Nube
     */
    public function processOrderCompleted(array $orderData): array
    {
        try {
            $results = [
                'processed' => 0,
                'errors' => []
            ];

            if (!isset($orderData['products']) || !is_array($orderData['products'])) {
                return [
                    'success' => false,
                    'error' => 'No se encontraron productos en la orden'
                ];
            }

            foreach ($orderData['products'] as $product) {
                $tiendanubeProductId = $product['product_id'] ?? null;
                $quantity = $product['quantity'] ?? 0;

                if (!$tiendanubeProductId || $quantity <= 0) {
                    continue;
                }

                // Buscar producto local por ID de Tienda Nube
                $inventory = SupplierInventory::where('tiendanube_product_id', $tiendanubeProductId)->first();

                if ($inventory) {
                    $newStock = max(0, $inventory->stock_quantity - $quantity);
                    $inventory->update(['stock_quantity' => $newStock]);
                    $inventory->updateStatus();
                    $results['processed']++;

                    Log::info("Stock actualizado para producto {$inventory->id}: -{$quantity} (nuevo stock: {$newStock})");
                } else {
                    $results['errors'][] = "Producto con Tienda Nube ID {$tiendanubeProductId} no encontrado localmente";
                }
            }

            return [
                'success' => true,
                'results' => $results
            ];
        } catch (Exception $e) {
            Log::error('Error procesando orden completada: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Preparar datos del producto para enviar a Tienda Nube
     */
    private function prepareProductData(SupplierInventory $inventory): array
    {
        $name = $inventory->product_name;
        $description = $inventory->description ?? '';

        // Agregar marca a la descripción si existe
        if ($inventory->distributorBrand) {
            $description = trim($description . "\n\nMarca: " . $inventory->distributorBrand->name);
        }

        $data = [
            'name' => [
                'es' => $name
            ],
            'description' => [
                'es' => $description
            ],
            'variants' => [
                [
                    'price' => $inventory->precio_menor ?? 0,
                    'stock' => $inventory->stock_quantity,
                    'sku' => $inventory->sku ?? null,
                ]
            ],
            'published' => true
        ];

        // Agregar categoría si existe
        if ($inventory->distributorCategory) {
            // Aquí podrías mapear la categoría local a una categoría de Tienda Nube
            // Por ahora, usamos el nombre como tag
            $data['tags'] = $inventory->distributorCategory->name;
        }

        return $data;
    }

    /**
     * Crear un producto en Tienda Nube
     */
    private function createProduct(array $productData, SupplierInventory $inventory): array
    {
        return $this->makeRequest('POST', '/products', $productData);
    }

    /**
     * Actualizar un producto en Tienda Nube
     */
    private function updateProduct(int $tiendanubeProductId, array $productData, SupplierInventory $inventory): array
    {
        // Para actualizar, solo enviamos los datos del producto (sin variantes en el root)
        $updateData = [
            'name' => $productData['name'],
            'description' => $productData['description'],
            'published' => $productData['published'] ?? true
        ];

        $result = $this->makeRequest('PUT', "/products/{$tiendanubeProductId}", $updateData);

        // Actualizar la variante por separado
        if ($result['success'] && $inventory->tiendanube_variant_id) {
            $variantData = $productData['variants'][0] ?? [];
            if (!empty($variantData)) {
                $this->makeRequest(
                    'PUT',
                    "/products/{$tiendanubeProductId}/variants/{$inventory->tiendanube_variant_id}",
                    $variantData
                );
            }
        }

        return $result;
    }

    /**
     * Realizar una petición a la API de Tienda Nube
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $url = "{$this->apiUrl}/{$this->storeId}{$endpoint}";

            $response = Http::withHeaders([
                'Authentication' => "bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
                'User-Agent' => 'AppTiziano (contacto@apptiziano.com)'
            ])->timeout(30);

            switch (strtoupper($method)) {
                case 'GET':
                    $response = $response->get($url, $data);
                    break;
                case 'POST':
                    $response = $response->post($url, $data);
                    break;
                case 'PUT':
                    $response = $response->put($url, $data);
                    break;
                case 'DELETE':
                    $response = $response->delete($url);
                    break;
                default:
                    throw new Exception("Método HTTP no soportado: {$method}");
            }

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            $errorMessage = $response->json('description') ?? $response->json('message') ?? 'Error desconocido';
            Log::warning("Error en API Tienda Nube [{$method} {$endpoint}]: " . $errorMessage);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $response->status()
            ];
        } catch (Exception $e) {
            Log::error("Excepción en API Tienda Nube [{$method} {$endpoint}]: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de sincronización
     */
    public function getStats(): array
    {
        return [
            'total_para_publicar' => SupplierInventory::forTiendaNube()->count(),
            'sincronizados' => SupplierInventory::forTiendaNube()->whereNotNull('tiendanube_product_id')->count(),
            'pendientes' => SupplierInventory::pendingTiendaNubeSync()->count(),
            'sin_sincronizar' => SupplierInventory::forTiendaNube()->whereNull('tiendanube_product_id')->count()
        ];
    }
}
