<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite agendar un turno para alguien que todavía no es cliente del sistema:
     * se guarda el nombre (y teléfono) sueltos en el turno, sin crear ficha de
     * cliente ni registro de cliente no frecuente. La decisión de registrarlo
     * queda para cuando se lo atiende.
     */
    public function up()
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->string('cliente_nombre')->nullable()->after('client_id');
            $table->string('cliente_telefono', 30)->nullable()->after('cliente_nombre');
        });
    }

    public function down()
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropColumn(['cliente_nombre', 'cliente_telefono']);
        });
    }
};
