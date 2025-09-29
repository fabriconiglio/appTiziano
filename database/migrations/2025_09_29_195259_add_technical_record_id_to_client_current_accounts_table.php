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
        Schema::table('client_current_accounts', function (Blueprint $table) {
            // MOD-030 (main): Agregar relación con technical_records para cuenta corriente de peluquería
            $table->unsignedBigInteger('technical_record_id')->nullable()->after('user_id');
            $table->foreign('technical_record_id')->references('id')->on('technical_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_current_accounts', function (Blueprint $table) {
            // MOD-030 (main): Remover relación con technical_records
            $table->dropForeign(['technical_record_id']);
            $table->dropColumn('technical_record_id');
        });
    }
};
