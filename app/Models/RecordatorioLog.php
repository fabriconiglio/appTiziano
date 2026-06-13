<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordatorioLog extends Model
{
    protected $table = 'recordatorios_log';

    protected $fillable = [
        'turno_id',
        'canal',
        'estado_envio',
        'respuesta',
        'enviado_en',
        'respondido_en',
    ];

    protected $casts = [
        'enviado_en' => 'datetime',
        'respondido_en' => 'datetime',
    ];

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }
}
