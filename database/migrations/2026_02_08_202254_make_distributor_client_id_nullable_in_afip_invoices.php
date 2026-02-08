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
            // Hacer distributor_client_id nullable para permitir Consumidor Final sin cliente
            $table->foreignId('distributor_client_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afip_invoices', function (Blueprint $table) {
            $table->foreignId('distributor_client_id')->nullable(false)->change();
        });
    }
};
