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
        Schema::create('distributor_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_client_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_inventory_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('product_name')->nullable(); // Para cuando se aplica a un producto específico
            $table->string('product_sku')->nullable(); // SKU del producto
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'gift'])->default('percentage');
            $table->decimal('discount_value', 10, 2)->default(0); // Porcentaje o cantidad fija
            $table->decimal('minimum_quantity', 10, 2)->default(1); // Cantidad mínima para aplicar descuento
            $table->decimal('minimum_amount', 10, 2)->nullable(); // Monto mínimo de compra
            $table->date('valid_from')->nullable(); // Fecha de inicio de validez
            $table->date('valid_until')->nullable(); // Fecha de fin de validez
            $table->boolean('is_active')->default(true);
            $table->boolean('applies_to_all_products')->default(false); // Si aplica a todos los productos del distribuidor
            $table->string('description')->nullable(); // Descripción del descuento
            $table->text('conditions')->nullable(); // Condiciones especiales
            $table->json('gift_products')->nullable(); // Productos de regalo (cuando discount_type = 'gift')
            $table->integer('max_uses')->nullable(); // Máximo número de usos
            $table->integer('current_uses')->default(0); // Número actual de usos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_discounts');
    }
};