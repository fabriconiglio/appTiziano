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
        Schema::create('distributor_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add the distributor_brand_id column to the products table
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('distributor_brand_id')
                  ->nullable()
                  ->after('category_id')
                  ->constrained('distributor_brands')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['distributor_brand_id']);
            $table->dropColumn('distributor_brand_id');
        });

        Schema::dropIfExists('distributor_brands');
    }
};
