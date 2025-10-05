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
        Schema::table('afip_invoices', function (Blueprint $table) {
            $table->foreignId('technical_record_id')->nullable()->after('distributor_client_id')->constrained('distributor_technical_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afip_invoices', function (Blueprint $table) {
            $table->dropForeign(['technical_record_id']);
            $table->dropColumn('technical_record_id');
        });
    }
};
