<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
