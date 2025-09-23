<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorClienteNoFrecuente extends Model
{
    protected $fillable = [
        'nombre',
        'telefono',
        'fecha',
        'monto',
        'productos',
        'products_purchased',
        'purchase_type',
        'observaciones',
        'user_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'products_purchased' => 'array'
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
     * Scope para filtrar por distribuidor
     */
    public function scopeDistribuidor($query, $distribuidor)
    {
        return $query->where('distribuidor', 'LIKE', "%{$distribuidor}%");
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }
}
