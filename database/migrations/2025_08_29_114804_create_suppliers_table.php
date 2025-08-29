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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del proveedor
            $table->string('contact_person')->nullable(); // Persona de contacto
            $table->string('email')->nullable(); // Email
            $table->string('phone')->nullable(); // Teléfono
            $table->text('address')->nullable(); // Dirección
            $table->string('cuit')->nullable(); // CUIT
            $table->string('business_name')->nullable(); // Razón social
            $table->string('payment_terms')->nullable(); // Condiciones de pago
            $table->string('delivery_time')->nullable(); // Tiempo de entrega
            $table->decimal('minimum_order', 10, 2)->nullable(); // Pedido mínimo
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Porcentaje de descuento
            $table->boolean('is_active')->default(true); // Estado activo/inactivo
            $table->text('notes')->nullable(); // Notas adicionales
            $table->string('website')->nullable(); // Sitio web
            $table->string('bank_account')->nullable(); // Cuenta bancaria
            $table->string('tax_category')->nullable(); // Categoría impositiva
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
