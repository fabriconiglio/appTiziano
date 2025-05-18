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
        'distributor_brand_id'
    ];

    protected $dates = [
        'last_restock_date'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'last_restock_date' => 'date'
    ];

    // Puedes añadir métodos personalizados según necesites

    public function isLowStock($threshold = 10)
    {
        return $this->stock_quantity <= $threshold;
    }

    public function isOutOfStock()
    {
        return $this->stock_quantity <= 0;
    }

    public function updateStatus()
    {
        if ($this->isOutOfStock()) {
            $this->status = 'out_of_stock';
        } elseif ($this->isLowStock()) {
            $this->status = 'low_stock';
        } else {
            $this->status = 'available';
        }

        return $this->save();
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
