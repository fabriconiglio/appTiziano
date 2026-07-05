<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('turnos', function (Blueprint $table) {
            // 'updated' del evento de Google que ya procesamos/empujamos.
            // Guard anti-loop: si el polling ve un evento con este mismo updated,
            // es el eco de un cambio hecho por el propio sistema y se ignora.
            $table->timestamp('google_updated_at')->nullable()->after('google_event_id');
            // De dónde nació el turno (por ahora siempre 'sistema'; queda para
            // una futura etapa de "turnos creados desde Google").
            $table->string('origen', 10)->default('sistema')->after('google_updated_at');

            $table->index('google_event_id');
        });
    }

    public function down()
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropIndex(['google_event_id']);
            $table->dropColumn(['google_updated_at', 'origen']);
        });
    }
};
