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
        Schema::table('hairdressing_supplier_purchases', function (Blueprint $table) {
            $table->index(['hairdressing_supplier_id', 'purchase_date'], 'hsp_supplier_date_idx');
            $table->index('receipt_number', 'hsp_receipt_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hairdressing_supplier_purchases', function (Blueprint $table) {
            $table->dropIndex('hsp_supplier_date_idx');
            $table->dropIndex('hsp_receipt_idx');
        });
    }
};
