<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->decimal('precio_mayor', 10, 2)->nullable()->after('price');
            $table->decimal('precio_menor', 10, 2)->nullable()->after('precio_mayor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->dropColumn('precio_mayor');
            $table->dropColumn('precio_menor');
        });
    }
};
