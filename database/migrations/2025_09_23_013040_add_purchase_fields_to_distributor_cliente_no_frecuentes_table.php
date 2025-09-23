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
            $table->string('purchase_type')->nullable()->after('monto');
            $table->json('products_purchased')->nullable()->after('purchase_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_cliente_no_frecuentes', function (Blueprint $table) {
            $table->dropColumn(['purchase_type', 'products_purchased']);
        });
    }
};
