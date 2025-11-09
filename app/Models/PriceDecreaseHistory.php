<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceDecreaseHistory extends Model
{
    protected $fillable = [
        'type',
        'decrease_value',
        'scope_type',
        'supplier_inventory_id',
        'distributor_brand_id',
        'user_id',
        'affected_products',
        'previous_values',
        'new_values',
        'price_types'
    ];

    protected $casts = [
        'decrease_value' => 'decimal:2',
        'affected_products' => 'array',
        'previous_values' => 'array',
        'new_values' => 'array',
        'price_types' => 'array'
    ];

    /**
     * Relación con el usuario que aplicó la disminución
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el producto (si es disminución individual)
     */
    public function supplierInventory(): BelongsTo
    {
        return $this->belongsTo(SupplierInventory::class);
    }

    /**
     * Relación con la marca (si es disminución por marca)
     */
    public function distributorBrand(): BelongsTo
    {
        return $this->belongsTo(DistributorBrand::class);
    }

    /**
     * Obtener el tipo de disminución formateado
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
        if ($this->scope_type === 'producto') {
            return 'Producto Individual';
        } elseif ($this->scope_type === 'marca') {
            return 'Por Marca';
        } else {
            return 'Varios Productos';
        }
    }

    /**
     * Obtener el valor formateado según el tipo
     */
    public function getValueFormattedAttribute(): string
    {
        if ($this->type === 'porcentual') {
            return number_format($this->decrease_value, 2) . '%';
        }
        return '$' . number_format($this->decrease_value, 2);
    }
}
