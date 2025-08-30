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
        Schema::create('supplier_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('purchase_date');
            $table->string('receipt_number');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('payment_amount', 10, 2);
            $table->decimal('balance_amount', 10, 2);
            $table->string('receipt_file');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['supplier_id', 'purchase_date']);
            $table->index('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_purchases');
    }
};
