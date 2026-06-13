<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            // Código de barras (solo distribuidora). Opcional: EAN-13/UPC de fábrica
            // o un EAN-13 interno generado a pedido. Único cuando hay valor.
            $table->string('codigo_barra')->nullable()->unique()->after('sku');
        });
    }

    public function down()
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->dropUnique(['codigo_barra']);
            $table->dropColumn('codigo_barra');
        });
    }
};
