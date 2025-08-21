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
        Schema::table('distributor_technical_records', function (Blueprint $table) {
            $table->decimal('advance_payment', 10, 2)->nullable()->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('distributor_technical_records', function (Blueprint $table) {
            $table->dropColumn('advance_payment');
        });
    }
};
