<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceIncreaseHistory extends Model
{
    protected $fillable = [
        'type',
        'increase_value',
        'scope_type',
        'supplier_inventory_id',
        'distributor_brand_id',
        'user_id',
        'affected_products',
        'previous_prices',
        'new_prices',
        'price_types'
    ];

    protected $casts = [
        'increase_value' => 'decimal:2',
        'affected_products' => 'array',
        'previous_prices' => 'array',
        'new_prices' => 'array',
        'price_types' => 'array'
    ];

    /**
     * Relación con el usuario que aplicó el aumento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el producto (si es aumento individual)
     */
    public function supplierInventory(): BelongsTo
    {
        return $this->belongsTo(SupplierInventory::class);
    }

    /**
     * Relación con la marca (si es aumento por marca)
     */
    public function distributorBrand(): BelongsTo
    {
        return $this->belongsTo(DistributorBrand::class);
    }

    /**
     * Obtener el tipo de aumento formateado
     */
    public function getTypeFormattedAttribute(): string
    {
        return $this->type === 'porcentual' ? 'Porcentual' : 'Fijo';
    }

    /**
     * Obtener el tipo de alcance formateado
     */
    public function getScopeTypeFormattedAttribute(): string
    {
        return $this->scope_type === 'producto' ? 'Producto Individual' : 'Por Marca';
    }

    /**
     * Obtener el valor formateado según el tipo
     */
    public function getValueFormattedAttribute(): string
    {
        if ($this->type === 'porcentual') {
            return number_format($this->increase_value, 2) . '%';
        }
        return '$' . number_format($this->increase_value, 2);
    }
}
