<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('distributor_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('quotation_number')->unique(); // Número de presupuesto único
            $table->datetime('quotation_date'); // Fecha del presupuesto
            $table->datetime('valid_until'); // Fálida hasta
            $table->string('quotation_type')->nullable(); // Tipo: al por mayor, al por menor, etc.
            $table->decimal('subtotal', 10, 2)->default(0); // Subtotal sin impuestos
            $table->decimal('tax_percentage', 5, 2)->default(21); // Porcentaje de IVA
            $table->decimal('tax_amount', 10, 2)->default(0); // Monto del IVA
            $table->decimal('total_amount', 10, 2)->default(0); // Total con impuestos
            $table->decimal('discount_percentage', 5, 2)->default(0); // Porcentaje de descuento
            $table->decimal('discount_amount', 10, 2)->default(0); // Monto del descuento
            $table->decimal('final_amount', 10, 2)->default(0); // Monto final después de descuentos
            $table->string('payment_terms')->nullable(); // Condiciones de pago
            $table->string('delivery_terms')->nullable(); // Condiciones de entrega
            $table->json('products_quoted')->nullable(); // Productos cotizados con cantidades y precios
            $table->text('observations')->nullable(); // Observaciones generales
            $table->text('terms_conditions')->nullable(); // Términos y condiciones
            $table->string('status')->default('active'); // Estado: active, expired, converted, cancelled
            $table->json('photos')->nullable(); // Fotos del presupuesto si las hay
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['distributor_client_id', 'status']);
            $table->index(['quotation_date', 'status']);
            $table->index('quotation_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('distributor_quotations');
    }
};
