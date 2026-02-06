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
        Schema::table('distributor_cliente_no_frecuentes', function (Blueprint $table) {
            $table->enum('forma_pago', ['efectivo', 'tarjeta', 'transferencia', 'deudor'])
                ->default('efectivo')
                ->after('monto')
                ->comment('Forma de pago de la venta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_cliente_no_frecuentes', function (Blueprint $table) {
            $table->dropColumn('forma_pago');
        });
    }
};
