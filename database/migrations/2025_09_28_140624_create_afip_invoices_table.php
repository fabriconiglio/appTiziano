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
        Schema::create('afip_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_client_id')->constrained('distributor_clients')->onDelete('cascade');
            $table->string('invoice_type'); // A, B, C
            $table->string('point_of_sale'); // Punto de venta
            $table->string('invoice_number'); // Número de factura
            $table->date('invoice_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('cae')->nullable(); // Código de Autorización Electrónica
            $table->date('cae_expiration')->nullable();
            $table->enum('status', ['draft', 'sent', 'authorized', 'rejected', 'cancelled'])->default('draft');
            $table->text('afip_response')->nullable(); // Respuesta completa de AFIP
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['distributor_client_id', 'invoice_date']);
            $table->index(['invoice_type', 'point_of_sale', 'invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afip_invoices');
    }
};
