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
        Schema::table('distributor_discounts', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('supplier_inventory_ids');
            $table->unsignedBigInteger('brand_id')->nullable()->after('category_id');
            $table->boolean('applies_to_category')->default(false)->after('brand_id');
            $table->boolean('applies_to_brand')->default(false)->after('applies_to_category');
            
            $table->foreign('category_id')->references('id')->on('distributor_categories')->onDelete('set null');
            $table->foreign('brand_id')->references('id')->on('distributor_brands')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_discounts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['category_id', 'brand_id', 'applies_to_category', 'applies_to_brand']);
        });
    }
};
