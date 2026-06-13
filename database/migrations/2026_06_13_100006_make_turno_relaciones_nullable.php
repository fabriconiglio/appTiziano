<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Peluquera y servicio pasan a ser opcionales en un turno (alta simple:
     * cliente + fecha). Se mantienen las FKs (permiten NULL).
     */
    public function up()
    {
        DB::statement('ALTER TABLE turnos MODIFY peluquera_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE turnos MODIFY servicio_id BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE turnos MODIFY peluquera_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE turnos MODIFY servicio_id BIGINT UNSIGNED NOT NULL');
    }
};
