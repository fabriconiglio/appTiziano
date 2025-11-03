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
        Schema::create('supplier_current_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('supplier_purchase_id')->nullable()->constrained('supplier_purchases')->onDelete('set null');
            $table->enum('type', ['debt', 'payment', 'credit']);
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->date('date');
            $table->string('reference')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['supplier_id', 'date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_current_accounts');
    }
};
