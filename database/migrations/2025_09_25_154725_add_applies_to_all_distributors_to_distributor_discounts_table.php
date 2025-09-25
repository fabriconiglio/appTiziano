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
            $table->boolean('applies_to_all_distributors')->default(false)->after('applies_to_all_products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributor_discounts', function (Blueprint $table) {
            $table->dropColumn('applies_to_all_distributors');
        });
    }
};
