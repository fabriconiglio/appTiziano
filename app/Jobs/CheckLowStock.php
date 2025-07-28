<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\SupplierInventory;
use App\Models\StockAlert;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLowStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $threshold;

    /**
     * Create a new job instance.
     */
    public function __construct(int $threshold = 5)
    {
        $this->threshold = $threshold;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // ===== INVENTARIO DE PELUQUERÍA =====
        $this->checkPeluqueriaInventory();
        
        // ===== INVENTARIO DE DISTRIBUIDORA =====
        $this->checkDistribuidoraInventory();
    }

    private function checkPeluqueriaInventory(): void
    {
        // Buscar productos con stock bajo
        $lowStockProducts = Product::where('current_stock', '<=', $this->threshold)
            ->where('current_stock', '>', 0)
            ->get();

        foreach ($lowStockProducts as $product) {
            $this->createAlert($product, 'low_stock', 'peluqueria');
        }

        // Verificar productos sin stock
        $outOfStockProducts = Product::where('current_stock', 0)->get();

        foreach ($outOfStockProducts as $product) {
            $this->createAlert($product, 'out_of_stock', 'peluqueria');
        }
    }

    private function checkDistribuidoraInventory(): void
    {
        // Buscar productos con stock bajo
        $lowStockProducts = SupplierInventory::where('stock_quantity', '<=', $this->threshold)
            ->where('stock_quantity', '>', 0)
            ->get();

        foreach ($lowStockProducts as $product) {
            $this->createAlert($product, 'low_stock', 'distribuidora');
        }

        // Verificar productos sin stock
        $outOfStockProducts = SupplierInventory::where('stock_quantity', 0)->get();

        foreach ($outOfStockProducts as $product) {
            $this->createAlert($product, 'out_of_stock', 'distribuidora');
        }
    }

    private function createAlert($product, string $type, string $inventoryType): void
    {
        $stockField = $inventoryType === 'peluqueria' ? 'current_stock' : 'stock_quantity';
        $currentStock = $product->$stockField;
        
        // Verificar si ya existe una alerta no leída para este producto
        $existingAlert = StockAlert::where('product_id', $product->id)
            ->where('type', $type)
            ->where('inventory_type', $inventoryType)
            ->where('is_read', false)
            ->first();

        if (!$existingAlert) {
            $productName = $inventoryType === 'peluqueria' ? $product->name : $product->product_name;
            $message = $type === 'low_stock' 
                ? "Stock bajo en {$productName} ({$inventoryType}): {$currentStock} unidades restantes"
                : "Producto agotado: {$productName} ({$inventoryType})";

            // Crear nueva alerta en la base de datos
            StockAlert::create([
                'product_id' => $product->id,
                'type' => $type,
                'current_stock' => $currentStock,
                'threshold' => $type === 'out_of_stock' ? 0 : $this->threshold,
                'message' => $message,
                'inventory_type' => $inventoryType
            ]);

            // Enviar notificación por email
            $users = User::all();
            foreach ($users as $user) {
                $user->notify(new LowStockAlert($product, $currentStock, $this->threshold, $inventoryType));
            }
        }
    }
}
