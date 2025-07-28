<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    protected $fillable = [
        'product_id',
        'type',
        'inventory_type',
        'current_stock',
        'threshold',
        'message',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'current_stock' => 'integer',
        'threshold' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplierInventory()
    {
        return $this->belongsTo(SupplierInventory::class, 'product_id');
    }

    public function getProductNameAttribute()
    {
        if ($this->inventory_type === 'peluqueria') {
            return $this->product ? $this->product->name : 'Producto no encontrado';
        } else {
            return $this->supplierInventory ? $this->supplierInventory->product_name : 'Producto no encontrado';
        }
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeLowStock($query)
    {
        return $query->where('type', 'low_stock');
    }

    public function scopeByInventoryType($query, $type)
    {
        return $query->where('inventory_type', $type);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }
}
