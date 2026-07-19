<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un turno pasa a poder tener varios servicios (antes: uno solo, servicio_id).
     * Se migran los servicio_id existentes a la tabla pivote y se elimina la
     * columna, que queda redundante.
     */
    public function up()
    {
        Schema::create('turno_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained('servicios')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['turno_id', 'servicio_id']);
        });

        DB::table('turnos')
            ->whereNotNull('servicio_id')
            ->select('id', 'servicio_id')
            ->get()
            ->each(function ($turno) {
                DB::table('turno_servicio')->insert([
                    'turno_id' => $turno->id,
                    'servicio_id' => $turno->servicio_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('turnos', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropColumn('servicio_id');
        });
    }

    public function down()
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
        });

        DB::table('turno_servicio')
            ->select('turno_id', 'servicio_id')
            ->get()
            ->each(function ($row) {
                DB::table('turnos')->where('id', $row->turno_id)->update(['servicio_id' => $row->servicio_id]);
            });

        Schema::dropIfExists('turno_servicio');
    }
};
