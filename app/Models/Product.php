<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'current_stock',
        'minimum_stock',
        'price'
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
