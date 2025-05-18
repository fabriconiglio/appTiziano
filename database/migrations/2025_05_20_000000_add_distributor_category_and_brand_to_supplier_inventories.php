<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->foreignId('distributor_category_id')
                ->nullable()
                ->after('id')
                ->constrained('distributor_categories')
                ->nullOnDelete();
            $table->foreignId('distributor_brand_id')
                ->nullable()
                ->after('distributor_category_id')
                ->constrained('distributor_brands')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->dropForeign(['distributor_category_id']);
            $table->dropColumn('distributor_category_id');
            $table->dropForeign(['distributor_brand_id']);
            $table->dropColumn('distributor_brand_id');
        });
    }
}; 