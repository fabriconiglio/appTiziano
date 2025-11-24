<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HairdressingSupplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'contact_person', 'email', 'phone', 'address', 'cuit', 'business_name',
        'payment_terms', 'delivery_time', 'minimum_order', 'discount_percentage',
        'is_active', 'notes', 'website', 'bank_account', 'tax_category'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'minimum_order' => 'decimal:2',
        'discount_percentage' => 'decimal:2'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'supplier_name', 'name');
    }

    public function getFullNameAttribute()
    {
        return $this->business_name ?: $this->name;
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active ? 'bg-success' : 'bg-danger';
    }

    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    public function getTotalInventoryValueAttribute()
    {
        return $this->products()->sum('price');
    }

    public function getLowStockProductsAttribute()
    {
        return $this->products()->where('current_stock', '<=', 5)->where('current_stock', '>', 0);
    }

    public function getOutOfStockProductsAttribute()
    {
        return $this->products()->where('current_stock', '<=', 0);
    }

    /**
     * Relación con las compras del proveedor de peluquería
     */
    public function hairdressingSupplierPurchases()
    {
        return $this->hasMany(HairdressingSupplierPurchase::class);
    }

    /**
     * Relación con la cuenta corriente del proveedor de peluquería
     */
    public function currentAccounts()
    {
        return $this->hasMany(HairdressingSupplierCurrentAccount::class);
    }

    /**
     * Obtener el total de deuda con el proveedor
     * Usa el mismo cálculo que current_balance para mantener consistencia
     */
    public function getTotalDebtAttribute()
    {
        $balance = $this->getCurrentBalance();
        // Solo retornar saldo positivo (deuda pendiente), 0 si hay crédito o está al día
        return max(0, $balance);
    }

    /**
     * Obtener el total pagado al proveedor
     */
    public function getTotalPaidAttribute()
    {
        return $this->hairdressingSupplierPurchases()->sum('payment_amount');
    }

    /**
     * Obtener el saldo actual de la cuenta corriente (como atributo)
     */
    public function getCurrentBalanceAttribute()
    {
        return HairdressingSupplierCurrentAccount::getCurrentBalance($this->id);
    }

    /**
     * Obtener el saldo formateado de la cuenta corriente (como atributo)
     */
    public function getFormattedBalanceAttribute()
    {
        return HairdressingSupplierCurrentAccount::getFormattedBalance($this->id);
    }

    /**
     * Verificar si tiene crédito (como atributo)
     */
    public function getHasCreditAttribute()
    {
        return HairdressingSupplierCurrentAccount::hasCredit($this->id);
    }

    /**
     * Obtener el crédito disponible (como atributo)
     */
    public function getAvailableCreditAttribute()
    {
        return HairdressingSupplierCurrentAccount::getAvailableCredit($this->id);
    }

    /**
     * Obtener el saldo actual de la cuenta corriente (como método)
     */
    public function getCurrentBalance()
    {
        return HairdressingSupplierCurrentAccount::getCurrentBalance($this->id);
    }

    /**
     * Obtener el saldo formateado de la cuenta corriente (como método)
     */
    public function getFormattedBalance()
    {
        return HairdressingSupplierCurrentAccount::getFormattedBalance($this->id);
    }

    /**
     * Verificar si tiene crédito (como método)
     */
    public function hasCredit()
    {
        return HairdressingSupplierCurrentAccount::hasCredit($this->id);
    }

    /**
     * Obtener el crédito disponible (como método)
     */
    public function getAvailableCredit()
    {
        return HairdressingSupplierCurrentAccount::getAvailableCredit($this->id);
    }
}
