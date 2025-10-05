<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('afip_invoice_items', function (Blueprint $table) {
            // Eliminar la foreign key existente
            $table->dropForeign(['product_id']);
            
            // Agregar la nueva foreign key apuntando a supplier_inventories
            $table->foreign('product_id')
                  ->references('id')
                  ->on('supplier_inventories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afip_invoice_items', function (Blueprint $table) {
            // Revertir a la foreign key original
            $table->dropForeign(['product_id']);
            
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });
    }
};
