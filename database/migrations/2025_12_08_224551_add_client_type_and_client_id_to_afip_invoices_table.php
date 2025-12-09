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
            // Agregar campos para otros tipos de clientes primero
            $table->string('client_type')->nullable()->after('distributor_client_id');
            $table->unsignedBigInteger('client_id')->nullable()->after('client_type');
            
            // Índice para búsquedas por tipo y ID de cliente
            $table->index(['client_type', 'client_id']);
        });
        
        // Hacer distributor_client_id nullable en una operación separada
        // Esto evita problemas con las constraints existentes
        Schema::table('afip_invoices', function (Blueprint $table) {
            $table->foreignId('distributor_client_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('afip_invoices', function (Blueprint $table) {
            $table->dropIndex(['client_type', 'client_id']);
            $table->dropColumn(['client_type', 'client_id']);
            $table->foreignId('distributor_client_id')->nullable(false)->change();
        });
    }
};
