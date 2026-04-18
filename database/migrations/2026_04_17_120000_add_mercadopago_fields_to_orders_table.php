<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('mercadopago','transfer') NOT NULL");

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('taca_taca_order_id');
            $table->string('mercadopago_preference_id')->nullable()->after('notes');
            $table->string('mercadopago_payment_id')->nullable()->after('mercadopago_preference_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['mercadopago_preference_id', 'mercadopago_payment_id']);
            $table->string('taca_taca_order_id')->nullable();
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('taca_taca','transfer') NOT NULL");
    }
};
