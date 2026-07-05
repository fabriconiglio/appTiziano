<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El cliente pasa a ser opcional: un turno creado desde Google Calendar
     * (evento con título "turno ...") nace "sin asignar" y se le completa el
     * cliente después desde el panel. Misma técnica que peluquera/servicio.
     */
    public function up()
    {
        DB::statement('ALTER TABLE turnos MODIFY client_id BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE turnos MODIFY client_id BIGINT UNSIGNED NOT NULL');
    }
};
