<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteNoFrecuente extends Model
{
    protected $fillable = [
        'nombre',
        'telefono',
        'fecha',
        'monto',
        'peluquero',
        'servicios',
        'observaciones',
        'user_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2'
    ];

    /**
     * Relación con el usuario que registró el cliente
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para filtrar por peluquero
     */
    public function scopePeluquero($query, $peluquero)
    {
        return $query->where('peluquero', 'LIKE', "%{$peluquero}%");
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }
}
