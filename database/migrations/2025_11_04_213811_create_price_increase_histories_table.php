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
        Schema::create('price_increase_histories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['porcentual', 'fijo'])->comment('Tipo de aumento: porcentual o fijo');
            $table->decimal('increase_value', 10, 2)->comment('Valor del aumento (porcentaje o monto fijo)');
            $table->enum('scope_type', ['producto', 'marca'])->comment('Tipo de alcance: producto individual o marca');
            $table->foreignId('supplier_inventory_id')->nullable()->constrained('supplier_inventories')->nullOnDelete()->comment('ID del producto si es aumento individual');
            $table->foreignId('distributor_brand_id')->nullable()->constrained('distributor_brands')->nullOnDelete()->comment('ID de la marca si es aumento por marca');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Usuario que aplicó el aumento');
            $table->json('affected_products')->nullable()->comment('IDs de productos afectados');
            $table->json('previous_prices')->nullable()->comment('Precios anteriores (precio_mayor y precio_menor por producto)');
            $table->json('new_prices')->nullable()->comment('Precios nuevos (precio_mayor y precio_menor por producto)');
            $table->json('price_types')->nullable()->comment('Tipos de precios afectados: precio_mayor, precio_menor o ambos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_increase_histories');
    }
};
