<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierInventory extends Model
{
    protected $fillable = [
        'product_name',
        'sku',
        'description',
        'price',
        'stock_quantity',
        'category',
        'brand',
        'supplier_name',
        'supplier_contact',
        'supplier_email',
        'supplier_phone',
        'last_restock_date',
        'purchase_price',
        'status',
        'notes',
        'distributor_category_id',
        'distributor_brand_id',
        'precio_mayor',
        'precio_menor',
        'costo'
    ];

    protected $dates = [
        'last_restock_date'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'precio_mayor' => 'decimal:2',
        'precio_menor' => 'decimal:2',
        'costo' => 'decimal:2',
        'stock_quantity' => 'integer',
        'last_restock_date' => 'date'
    ];

    // Puedes añadir métodos personalizados según necesites

    public function isLowStock($threshold = 5)
    {
        return $this->stock_quantity <= $threshold && $this->stock_quantity > 0;
    }

    public function isOutOfStock()
    {
        return $this->stock_quantity <= 0;
    }

    public function updateStatus()
    {
        if ($this->isOutOfStock()) {
            $this->status = 'out_of_stock';
        } elseif ($this->isLowStock(5)) {
            $this->status = 'low_stock';
        } else {
            $this->status = 'available';
        }

        return $this->save();
    }

    public function getStatusTextAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'Sin stock';
        } elseif ($this->isLowStock(5)) {
            return 'Bajo stock';
        } else {
            return 'Disponible';
        }
    }

    public function getStatusBadgeClassAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'bg-danger';
        } elseif ($this->isLowStock(5)) {
            return 'bg-warning text-dark';
        } else {
            return 'bg-success';
        }
    }

    public function distributorCategory()
    {
        return $this->belongsTo(DistributorCategory::class);
    }

    public function distributorBrand()
    {
        return $this->belongsTo(DistributorBrand::class);
    }
}
