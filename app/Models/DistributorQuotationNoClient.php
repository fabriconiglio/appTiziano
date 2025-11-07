<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorQuotationNoClient extends Model
{
    protected $fillable = [
        'user_id',
        'nombre',
        'telefono',
        'email',
        'direccion',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'quotation_type',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'discount_percentage',
        'discount_amount',
        'final_amount',
        'payment_terms',
        'delivery_terms',
        'products_quoted',
        'observations',
        'terms_conditions',
        'status',
        'photos'
    ];

    protected $casts = [
        'products_quoted' => 'array',
        'photos' => 'array',
        'quotation_date' => 'datetime',
        'valid_until' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2'
    ];

    // Estados del presupuesto
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    // Tipos de presupuesto
    const TYPE_MAYOR = 'al_por_mayor';
    const TYPE_MENOR = 'al_por_menor';

    /**
     * Relación con el usuario que creó el presupuesto
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si el presupuesto está vencido
     */
    public function isExpired()
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    /**
     * Verificar si el presupuesto está activo
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE && !$this->isExpired();
    }

    /**
     * Obtener el estado formateado
     */
    public function getStatusFormattedAttribute()
    {
        $statuses = [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_EXPIRED => 'Vencido',
            self::STATUS_CANCELLED => 'Cancelado'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Obtener el tipo formateado
     */
    public function getTypeFormattedAttribute()
    {
        $types = [
            self::TYPE_MAYOR => 'Al Por Mayor',
            self::TYPE_MENOR => 'Al Por Menor'
        ];

        return $types[$this->quotation_type] ?? $this->quotation_type;
    }

    /**
     * Calcular automáticamente los montos
     */
    public function calculateAmounts()
    {
        // Calcular subtotal
        $this->subtotal = 0;
        if (!empty($this->products_quoted)) {
            foreach ($this->products_quoted as $product) {
                $this->subtotal += ($product['price'] * $product['quantity']);
            }
        }

        // Calcular IVA
        $this->tax_amount = $this->subtotal * ($this->tax_percentage / 100);

        // Calcular total con IVA
        $this->total_amount = $this->subtotal + $this->tax_amount;

        // Calcular descuento
        $this->discount_amount = $this->total_amount * ($this->discount_percentage / 100);

        // Calcular monto final
        $this->final_amount = $this->total_amount - $this->discount_amount;

        return $this;
    }

    /**
     * Generar número de presupuesto automáticamente
     */
    public static function generateQuotationNumber()
    {
        $lastQuotation = self::orderBy('id', 'desc')->first();
        $lastNumber = $lastQuotation ? (int) str_replace('PNC', '', $lastQuotation->quotation_number) : 0;
        return 'PNC' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope para presupuestos activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope para presupuestos vencidos
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('valid_until', '<', now());
    }

    /**
     * Obtener el nombre completo del cliente
     */
    public function getClientNameAttribute()
    {
        return $this->nombre ?? 'Cliente no especificado';
    }
}

