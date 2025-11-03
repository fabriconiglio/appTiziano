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
        Schema::create('hairdressing_supplier_current_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hairdressing_supplier_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('hairdressing_supplier_purchase_id')->nullable();
            $table->enum('type', ['debt', 'payment', 'credit']);
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->date('date');
            $table->string('reference')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
            
            // Foreign keys con nombres más cortos
            $table->foreign('hairdressing_supplier_id', 'hscc_hs_id_fk')->references('id')->on('hairdressing_suppliers')->onDelete('cascade');
            $table->foreign('hairdressing_supplier_purchase_id', 'hscc_hsp_id_fk')->references('id')->on('hairdressing_supplier_purchases')->onDelete('set null');
            
            // Índices para mejorar el rendimiento
            $table->index(['hairdressing_supplier_id', 'date'], 'hscc_hs_date_idx');
            $table->index('type', 'hscc_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hairdressing_supplier_current_accounts');
    }
};
