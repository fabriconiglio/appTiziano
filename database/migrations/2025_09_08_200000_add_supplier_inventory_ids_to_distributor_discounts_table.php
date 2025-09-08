<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributor_discounts', function (Blueprint $table) {
            // Lista de productos del inventario a los que aplica el descuento
            $table->json('supplier_inventory_ids')->nullable()->after('supplier_inventory_id');
        });
    }

    public function down(): void
    {
        Schema::table('distributor_discounts', function (Blueprint $table) {
            $table->dropColumn('supplier_inventory_ids');
        });
    }
};
