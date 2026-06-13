<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plantillas_whatsapp', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            // Identificador de la plantilla aprobada por Meta / Content SID de Twilio.
            $table->string('sid')->nullable();
            $table->text('cuerpo');
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plantillas_whatsapp');
    }
};
