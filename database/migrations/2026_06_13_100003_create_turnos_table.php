<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('peluquera_id')->constrained('peluqueras')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->dateTime('inicia_en');
            $table->dateTime('termina_en');
            $table->enum('estado', ['pendiente', 'confirmado', 'cancelado'])->default('pendiente');
            $table->string('color', 20)->nullable();
            $table->text('notas')->nullable();
            // Id del evento espejo en Google Calendar (sync de una vía).
            $table->string('google_event_id')->nullable();
            $table->timestamps();

            $table->index(['peluquera_id', 'inicia_en']);
            $table->index('inicia_en');
        });
    }

    public function down()
    {
        Schema::dropIfExists('turnos');
    }
};
