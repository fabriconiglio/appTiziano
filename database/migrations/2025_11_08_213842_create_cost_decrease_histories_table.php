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
        Schema::create('cost_decrease_histories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['porcentual', 'fijo'])->comment('Tipo de disminución: porcentual o fijo');
            $table->decimal('decrease_value', 10, 2)->comment('Valor de la disminución (porcentaje o monto fijo)');
            $table->enum('scope_type', ['producto', 'marca', 'multiples'])->comment('Tipo de alcance: producto individual, marca o varios productos');
            $table->foreignId('supplier_inventory_id')->nullable()->constrained('supplier_inventories')->nullOnDelete()->comment('ID del producto si es disminución individual');
            $table->foreignId('distributor_brand_id')->nullable()->constrained('distributor_brands')->nullOnDelete()->comment('ID de la marca si es disminución por marca');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Usuario que aplicó la disminución');
            $table->json('affected_products')->nullable()->comment('IDs de productos afectados');
            $table->json('previous_values')->nullable()->comment('Valores anteriores (costo por producto)');
            $table->json('new_values')->nullable()->comment('Valores nuevos (costo por producto)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_decrease_histories');
    }
};
