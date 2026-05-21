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
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->decimal('peso_gramos', 10, 2)->unsigned()->nullable()->change();
            $table->decimal('volumen_cm3', 10, 2)->unsigned()->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->unsignedInteger('peso_gramos')->nullable()->change();
            $table->unsignedInteger('volumen_cm3')->nullable()->change();
        });
    }
};
