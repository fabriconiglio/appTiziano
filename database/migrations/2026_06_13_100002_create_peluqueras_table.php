<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('peluqueras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('color', 20)->default('#28a745');
            // Horarios de atención por día. Ej:
            // {"1":["09:00","20:00"], "2":["09:00","20:00"], ... "0":null (domingo cerrado)}
            $table->json('horarios')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('peluqueras');
    }
};
