<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'current_stock',
        'minimum_stock',
        'price',
        'category_id',
        'brand_id', // Agregamos el brand_id a los campos fillable
        'supplier_name',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier()
    {
        return $this->belongsTo(HairdressingSupplier::class, 'supplier_name', 'name');
    }
}
