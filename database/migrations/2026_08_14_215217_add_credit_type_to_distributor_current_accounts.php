<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Suma el movimiento 'credit' (nota de crédito por devolución) a la cuenta
 * corriente. El saldo pasa a ser deudas - pagos - créditos, así que si el
 * cliente devuelve más de lo que debía queda en negativo: saldo a favor.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cambiar un enum necesita SQL crudo: doctrine/dbal no viene en el proyecto.
        DB::statement("ALTER TABLE distributor_current_accounts MODIFY COLUMN type ENUM('debt', 'payment', 'credit') NOT NULL");

        Schema::table('distributor_current_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('distributor_return_id')->nullable()->after('distributor_technical_record_id');

            $table->foreign('distributor_return_id', 'fk_current_accounts_return')
                ->references('id')->on('distributor_returns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('distributor_current_accounts', function (Blueprint $table) {
            $table->dropForeign('fk_current_accounts_return');
            $table->dropColumn('distributor_return_id');
        });

        // Los movimientos 'credit' se pasan a 'payment' para no perder el saldo:
        // los dos restan, así que el total del cliente queda igual.
        DB::table('distributor_current_accounts')->where('type', 'credit')->update(['type' => 'payment']);

        DB::statement("ALTER TABLE distributor_current_accounts MODIFY COLUMN type ENUM('debt', 'payment') NOT NULL");
    }
};
