<?php

namespace App\Console\Commands;

use App\Models\SupplierInventory;
use Illuminate\Console\Command;

class UpdateSupplierInventoryStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supplier-inventory:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar el estado de todos los productos del inventario de distribuidora';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Actualizando estados del inventario de distribuidora...');
        
        $inventories = SupplierInventory::all();
        $updated = 0;
        
        foreach ($inventories as $inventory) {
            $oldStatus = $inventory->status;
            $inventory->updateStatus();
            
            if ($oldStatus !== $inventory->status) {
                $this->line("Producto: {$inventory->product_name} - Stock: {$inventory->stock_quantity} - Estado: {$oldStatus} → {$inventory->status}");
                $updated++;
            }
        }
        
        $this->info("Proceso completado. {$updated} productos actualizados.");
        
        // Mostrar resumen
        $this->info("\nResumen de estados:");
        $this->info("- Disponible: " . SupplierInventory::where('status', 'available')->count());
        $this->info("- Bajo stock: " . SupplierInventory::where('status', 'low_stock')->count());
        $this->info("- Sin stock: " . SupplierInventory::where('status', 'out_of_stock')->count());
        
        return 0;
    }
}
