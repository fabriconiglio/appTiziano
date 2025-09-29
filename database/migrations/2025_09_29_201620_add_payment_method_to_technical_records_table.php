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
        Schema::table('technical_records', function (Blueprint $table) {
            // MOD-030 (main): Agregar campo payment_method para el método de pago
            $table->string('payment_method')->nullable()->after('service_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technical_records', function (Blueprint $table) {
            // MOD-030 (main): Remover campo payment_method
            $table->dropColumn('payment_method');
        });
    }
};
