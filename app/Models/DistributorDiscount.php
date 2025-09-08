<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DistributorDiscount extends Model
{
    protected $fillable = [
        'distributor_client_id',
        'distributor_client_ids',
        'supplier_inventory_id',
        'product_name',
        'product_sku',
        'discount_type',
        'discount_value',
        'minimum_quantity',
        'minimum_amount',
        'valid_from',
        'valid_until',
        'is_active',
        'applies_to_all_products',
        'description',
        'conditions',
        'gift_products',
        'supplier_inventory_ids',
        'max_uses',
        'current_uses'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
        'applies_to_all_products' => 'boolean',
        'gift_products' => 'array',
        'supplier_inventory_ids' => 'array',
        'distributor_client_ids' => 'array',
        'max_uses' => 'integer',
        'current_uses' => 'integer' 
    ];

    // Tipos de descuento
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED_AMOUNT = 'fixed_amount';
    const TYPE_GIFT = 'gift';

    /**
     * Relación con el cliente distribuidor
     */
    public function distributorClient(): BelongsTo
    {
        return $this->belongsTo(DistributorClient::class);
    }

    /**
     * Relación con el inventario de proveedores
     */
    public function supplierInventory(): BelongsTo
    {
        return $this->belongsTo(SupplierInventory::class);
    }

    /**
     * Verificar si el descuento está vigente
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = Carbon::now()->startOfDay();

        // Verificar fechas de validez
        if ($this->valid_from && $this->valid_from->startOfDay()->gt($now)) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->startOfDay()->lt($now)) {
            return false;
        }

        // Verificar límite de usos
        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Verificar si el descuento aplica a un producto específico
     */
    public function appliesTo($productSku = null, $productName = null, $productId = null): bool
    {

        // Si aplica a todos los productos del distribuidor
        if ($this->applies_to_all_products) {
            return true;
        }

        // Si hay productos específicos en el inventario (array)
        if (!empty($this->supplier_inventory_ids)) {
            // Si el descuento está configurado para un producto específico por ID
            if ($productId && in_array((string)$productId, $this->supplier_inventory_ids)) {
                return true;
            }
            
            $supplierInventories = SupplierInventory::whereIn('id', $this->supplier_inventory_ids)->get();
            
            foreach ($supplierInventories as $inventory) {
                // Comparar por nombre si el SKU es null
                if ($productSku === null) {
                    if ($inventory->product_name === $productName) {
                        return true;
                    }
                } else if ($inventory->sku === $productSku || $inventory->product_name === $productName) {
                    return true;
                }
            }
        }
        
        // Compatibilidad: verificar también el campo singular
        if ($this->supplier_inventory_id && $this->supplierInventory) {
            $matches = $this->supplierInventory->sku === $productSku || 
                      $this->supplierInventory->product_name === $productName;
            return $matches;
        }

        // Si hay SKU o nombre específico
        if ($this->product_sku && $this->product_sku === $productSku) {
            return true;
        }

        if ($this->product_name && $this->product_name === $productName) {
            return true;
        }

        return false;
    }

    /**
     * Calcular el descuento aplicable
     */
    public function calculateDiscount($quantity, $unitPrice): array
    {
        if (!$this->isValid()) {
            return [
                'discount_amount' => 0,
                'final_price' => $unitPrice * $quantity,
                'gift_products' => [],
                'message' => 'Descuento no válido'
            ];
        }

        // Verificar cantidad mínima
        if ($quantity < $this->minimum_quantity) {
            return [
                'discount_amount' => 0,
                'final_price' => $unitPrice * $quantity,
                'gift_products' => [],
                'message' => "Cantidad mínima requerida: {$this->minimum_quantity}"
            ];
        }

        $subtotal = $unitPrice * $quantity;

        // Verificar monto mínimo
        if ($this->minimum_amount && $subtotal < $this->minimum_amount) {
            return [
                'discount_amount' => 0,
                'final_price' => $subtotal,
                'gift_products' => [],
                'message' => "Monto mínimo requerido: $" . number_format($this->minimum_amount, 2)
            ];
        }

        switch ($this->discount_type) {
            case self::TYPE_PERCENTAGE:
                $discountAmount = $subtotal * ($this->discount_value / 100);
                return [
                    'discount_amount' => $discountAmount,
                    'final_price' => $subtotal - $discountAmount,
                    'gift_products' => [],
                    'message' => "Descuento del {$this->discount_value}% aplicado"
                ];

            case self::TYPE_FIXED_AMOUNT:
                $discountAmount = min($this->discount_value, $subtotal);
                return [
                    'discount_amount' => $discountAmount,
                    'final_price' => $subtotal - $discountAmount,
                    'gift_products' => [],
                    'message' => "Descuento de $" . number_format($discountAmount, 2) . " aplicado"
                ];

            case self::TYPE_GIFT:
                return [
                    'discount_amount' => $subtotal, // Descuento del 100% (precio completo)
                    'final_price' => 0, // Precio final es 0 (gratis)
                    'gift_products' => $this->gift_products ?? [],
                    'message' => "Producto de regalo - Descuento del 100% aplicado"
                ];

            default:
                return [
                    'discount_amount' => 0,
                    'final_price' => $subtotal,
                    'gift_products' => [],
                    'message' => 'Tipo de descuento no válido'
                ];
        }
    }

    /**
     * Incrementar el contador de usos
     */
    public function incrementUsage(): void
    {
        $this->increment('current_uses');
    }

    /**
     * Obtener el texto descriptivo del tipo de descuento
     */
    public function getDiscountTypeTextAttribute(): string
    {
        switch ($this->discount_type) {
            case self::TYPE_PERCENTAGE:
                return "Porcentaje ({$this->discount_value}%)";
            case self::TYPE_FIXED_AMOUNT:
                return "Monto fijo ($" . number_format($this->discount_value, 2) . ")";
            case self::TYPE_GIFT:
                return "Regalo";
            default:
                return "Desconocido";
        }
    }

    /**
     * Obtener el estado del descuento
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactivo';
        }

        if (!$this->isValid()) {
            return 'Expirado';
        }

        return 'Activo';
    }

    /**
     * Obtener la clase CSS para el badge de estado
     */
    public function getStatusBadgeClassAttribute(): string
    {
        switch ($this->status) {
            case 'Activo':
                return 'bg-success';
            case 'Expirado':
                return 'bg-warning';
            case 'Inactivo':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Scope para descuentos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para descuentos válidos (activos y dentro de fechas)
     */
    public function scopeValid($query)
    {
        $now = Carbon::now()->toDateString();
        
        return $query->where('is_active', true)
                    ->where(function($q) use ($now) {
                        $q->whereNull('valid_from')
                          ->orWhereDate('valid_from', '<=', $now);
                    })
                    ->where(function($q) use ($now) {
                        $q->whereNull('valid_until')
                          ->orWhereDate('valid_until', '>=', $now);
                    })
                    ->where(function($q) {
                        $q->whereNull('max_uses')
                          ->orWhereColumn('current_uses', '<', 'max_uses');
                    });
    }

    /**
     * Scope para descuentos de un distribuidor específico
     */
    public function scopeForDistributor($query, $distributorClientId)
    {
        return $query->where(function($q) use ($distributorClientId) {
            $q->where('distributor_client_id', $distributorClientId)
              ->orWhereRaw("JSON_CONTAINS(distributor_client_ids, ?)", [$distributorClientId]);
        });
    }
}