<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->unsignedInteger('peso_gramos')->nullable()->after('is_featured');
            $table->unsignedInteger('volumen_cm3')->nullable()->after('peso_gramos');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->dropColumn(['peso_gramos', 'volumen_cm3']);
        });
    }
};
