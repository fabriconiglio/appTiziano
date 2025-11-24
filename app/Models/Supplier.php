<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'cuit',
        'business_name',
        'payment_terms',
        'delivery_time',
        'minimum_order',
        'discount_percentage',
        'is_active',
        'notes',
        'website',
        'bank_account',
        'tax_category'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'minimum_order' => 'decimal:2',
        'discount_percentage' => 'decimal:2'
    ];

    /**
     * Relación con el inventario de proveedores
     */
    public function supplierInventories()
    {
        return $this->hasMany(SupplierInventory::class, 'supplier_name', 'name');
    }

    /**
     * Obtener el nombre completo del proveedor
     */
    public function getFullNameAttribute()
    {
        return $this->business_name ?: $this->name;
    }

    /**
     * Obtener el estado formateado
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Obtener la clase del badge de estado
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active ? 'bg-success' : 'bg-danger';
    }

    /**
     * Obtener el total de productos del proveedor
     */
    public function getProductsCountAttribute()
    {
        return $this->supplierInventories()->count();
    }

    /**
     * Obtener el valor total del inventario del proveedor
     */
    public function getTotalInventoryValueAttribute()
    {
        return $this->supplierInventories()
            ->where('stock_quantity', '>', 0)
            ->get()
            ->sum(function($item) {
                return $item->stock_quantity * ($item->costo ?: 0);
            });
    }

    /**
     * Obtener productos con bajo stock
     */
    public function getLowStockProductsAttribute()
    {
        return $this->supplierInventories()
            ->where('stock_quantity', '<=', 5)
            ->where('stock_quantity', '>', 0)
            ->get();
    }

    /**
     * Obtener productos sin stock
     */
    public function getOutOfStockProductsAttribute()
    {
        return $this->supplierInventories()
            ->where('stock_quantity', '<=', 0)
            ->get();
    }

    /**
     * Relación con las compras del proveedor
     */
    public function supplierPurchases()
    {
        return $this->hasMany(SupplierPurchase::class);
    }

    /**
     * Relación con la cuenta corriente del proveedor
     */
    public function currentAccounts()
    {
        return $this->hasMany(SupplierCurrentAccount::class);
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
        return $this->supplierPurchases()->sum('payment_amount');
    }

    /**
     * Obtener el saldo actual de la cuenta corriente (como atributo)
     */
    public function getCurrentBalanceAttribute()
    {
        return SupplierCurrentAccount::getCurrentBalance($this->id);
    }

    /**
     * Obtener el saldo formateado de la cuenta corriente (como atributo)
     */
    public function getFormattedBalanceAttribute()
    {
        return SupplierCurrentAccount::getFormattedBalance($this->id);
    }

    /**
     * Verificar si tiene crédito (como atributo)
     */
    public function getHasCreditAttribute()
    {
        return SupplierCurrentAccount::hasCredit($this->id);
    }

    /**
     * Obtener el crédito disponible (como atributo)
     */
    public function getAvailableCreditAttribute()
    {
        return SupplierCurrentAccount::getAvailableCredit($this->id);
    }

    /**
     * Obtener el saldo actual de la cuenta corriente (como método)
     */
    public function getCurrentBalance()
    {
        return SupplierCurrentAccount::getCurrentBalance($this->id);
    }

    /**
     * Obtener el saldo formateado de la cuenta corriente (como método)
     */
    public function getFormattedBalance()
    {
        return SupplierCurrentAccount::getFormattedBalance($this->id);
    }

    /**
     * Verificar si tiene crédito (como método)
     */
    public function hasCredit()
    {
        return SupplierCurrentAccount::hasCredit($this->id);
    }

    /**
     * Obtener el crédito disponible (como método)
     */
    public function getAvailableCredit()
    {
        return SupplierCurrentAccount::getAvailableCredit($this->id);
    }
} 