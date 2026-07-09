<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Carga inicial de servicios de la peluquería (pedido de la clienta).
     * Idempotente: solo inserta los que no existen por nombre.
     */
    public function up()
    {
        $servicios = [
            'Alisado',
            'Antifriz',
            'Color',
            'Balayage',
            'Corte dama',
            'Corte caballero',
            'Permanente',
            'Brushing',
            'Plancha',
            'Extensiones',
            'Corte de rulos',
            'Nutrición loreal',
            'Nutrición común',
            'Ampolla',
            'Reflejos',
            'Mechas',
        ];

        foreach ($servicios as $nombre) {
            $existe = DB::table('servicios')->where('nombre', $nombre)->exists();
            if (! $existe) {
                DB::table('servicios')->insert([
                    'nombre' => $nombre,
                    'duracion_minutos' => 30,
                    'precio_base' => 0,
                    'color_default' => '#3788d8',
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        // No borra datos: los servicios pueden haber sido editados/usados en turnos.
    }
};
