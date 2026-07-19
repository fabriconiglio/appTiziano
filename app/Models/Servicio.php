<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'duracion_minutos',
        'precio_base',
        'color_default',
        'activo',
    ];

    protected $casts = [
        'precio_base' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function turnos()
    {
        return $this->belongsToMany(Turno::class, 'turno_servicio');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
