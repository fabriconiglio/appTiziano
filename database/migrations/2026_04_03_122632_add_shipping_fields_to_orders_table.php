<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_name')->nullable()->after('notes');
            $table->string('shipping_phone')->nullable()->after('shipping_name');
            $table->string('shipping_province')->nullable()->after('shipping_phone');
            $table->string('shipping_city')->nullable()->after('shipping_province');
            $table->string('shipping_zip')->nullable()->after('shipping_city');
            $table->string('shipping_address')->nullable()->after('shipping_zip');
            $table->string('shipping_address_2')->nullable()->after('shipping_address');
            $table->enum('shipping_method', ['local_pickup', 'cordoba', 'national'])->default('local_pickup')->after('shipping_address_2');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_name',
                'shipping_phone',
                'shipping_province',
                'shipping_city',
                'shipping_zip',
                'shipping_address',
                'shipping_address_2',
                'shipping_method',
            ]);
        });
    }
};
