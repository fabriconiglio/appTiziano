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
        Schema::table('technical_records', function (Blueprint $table) {
            $table->decimal('service_cost', 10, 2)->default(0.00)->after('service_date');
            $table->string('service_type')->nullable()->after('service_cost');
            $table->text('service_description')->nullable()->after('service_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('technical_records', function (Blueprint $table) {
            $table->dropColumn(['service_cost', 'service_type', 'service_description']);
        });
    }
}; 