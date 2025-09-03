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
        // Hacer nullable el campo receipt_file en supplier_purchases
        Schema::table('supplier_purchases', function (Blueprint $table) {
            $table->string('receipt_file')->nullable()->change();
        });

        // Hacer nullable el campo receipt_file en hairdressing_supplier_purchases
        Schema::table('hairdressing_supplier_purchases', function (Blueprint $table) {
            $table->string('receipt_file')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el campo receipt_file a no nullable en supplier_purchases
        Schema::table('supplier_purchases', function (Blueprint $table) {
            $table->string('receipt_file')->nullable(false)->change();
        });

        // Revertir el campo receipt_file a no nullable en hairdressing_supplier_purchases
        Schema::table('hairdressing_supplier_purchases', function (Blueprint $table) {
            $table->string('receipt_file')->nullable(false)->change();
        });
    }
};
