<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peluquera extends Model
{
    protected $table = 'peluqueras';

    protected $fillable = [
        'nombre',
        'color',
        'horarios',
        'activo',
    ];

    protected $casts = [
        'horarios' => 'array',
        'activo' => 'boolean',
    ];

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
