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
        Schema::table('supplier_inventories', function (Blueprint $table) {
            // Campo para imágenes del producto
            $table->json('images')->nullable()->after('notes');
            
            // Campos para integración con Tienda Nube
            $table->boolean('publicar_tiendanube')->default(false)->after('images');
            $table->unsignedBigInteger('tiendanube_product_id')->nullable()->after('publicar_tiendanube');
            $table->unsignedBigInteger('tiendanube_variant_id')->nullable()->after('tiendanube_product_id');
            $table->timestamp('tiendanube_synced_at')->nullable()->after('tiendanube_variant_id');
            
            // Índice para búsquedas por producto de Tienda Nube
            $table->index('tiendanube_product_id');
            $table->index('publicar_tiendanube');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->dropIndex(['tiendanube_product_id']);
            $table->dropIndex(['publicar_tiendanube']);
            
            $table->dropColumn([
                'images',
                'publicar_tiendanube',
                'tiendanube_product_id',
                'tiendanube_variant_id',
                'tiendanube_synced_at'
            ]);
        });
    }
};
