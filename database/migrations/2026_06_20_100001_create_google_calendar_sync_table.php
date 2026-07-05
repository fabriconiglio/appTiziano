<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Estado del polling incremental contra Google Calendar (sync bidireccional).
        // Guarda el syncToken que Google devuelve en events.list para pedir "solo lo
        // que cambió desde la última vez".
        Schema::create('google_calendar_sync', function (Blueprint $table) {
            $table->id();
            $table->string('calendar_id')->unique();
            $table->text('sync_token')->nullable();
            $table->timestamp('ultima_sync_en')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('google_calendar_sync');
    }
};
