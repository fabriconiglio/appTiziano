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
        Schema::create('distributor_quotation_no_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Datos del cliente no registrado
            $table->string('nombre')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();
            
            // Información del presupuesto
            $table->string('quotation_number')->unique();
            $table->datetime('quotation_date');
            $table->datetime('valid_until');
            $table->string('quotation_type')->nullable(); // al_por_mayor, al_por_menor
            
            // Montos
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(21);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            
            // Condiciones
            $table->string('payment_terms')->nullable();
            $table->string('delivery_terms')->nullable();
            
            // Productos y observaciones
            $table->json('products_quoted')->nullable();
            $table->text('observations')->nullable();
            $table->text('terms_conditions')->nullable();
            
            // Estado y fotos
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->json('photos')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['status', 'quotation_date']);
            $table->index('quotation_number');
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_quotation_no_clients');
    }
};
