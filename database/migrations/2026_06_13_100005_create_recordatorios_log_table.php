<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recordatorios_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->enum('canal', ['whatsapp', 'email'])->default('whatsapp');
            $table->enum('estado_envio', ['enviado', 'fallido', 'pendiente'])->default('pendiente');
            $table->string('respuesta')->nullable(); // SI / NO / otro
            $table->timestamp('enviado_en')->nullable();
            $table->timestamp('respondido_en')->nullable();
            $table->timestamps();

            $table->index('turno_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('recordatorios_log');
    }
};
