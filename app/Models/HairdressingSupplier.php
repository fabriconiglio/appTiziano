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
     * Obtener el total de deuda con el proveedor
     */
    public function getTotalDebtAttribute()
    {
        return $this->hairdressingSupplierPurchases()->sum('balance_amount');
    }

    /**
     * Obtener el total pagado al proveedor
     */
    public function getTotalPaidAttribute()
    {
        return $this->hairdressingSupplierPurchases()->sum('payment_amount');
    }
}
