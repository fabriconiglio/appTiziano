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
        Schema::create('afip_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('afip_invoice_id')->constrained('afip_invoices')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('description');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(21.00); // IVA por defecto
            $table->decimal('tax_amount', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
            
            $table->index(['afip_invoice_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afip_invoice_items');
    }
};
