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
        'costo',
        'images',
        'publicar_tiendanube',
        'tiendanube_product_id',
        'tiendanube_variant_id',
        'tiendanube_synced_at'
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
        'last_restock_date' => 'date',
        'images' => 'array',
        'publicar_tiendanube' => 'boolean',
        'tiendanube_synced_at' => 'datetime'
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_name', 'name');
    }

    /**
     * Obtener la imagen principal del producto
     */
    public function getMainImageAttribute()
    {
        if (!empty($this->images) && is_array($this->images)) {
            return $this->images[0] ?? null;
        }
        return null;
    }

    /**
     * Obtener URLs públicas de las imágenes
     */
    public function getImageUrlsAttribute()
    {
        if (empty($this->images) || !is_array($this->images)) {
            return [];
        }

        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $this->images);
    }

    /**
     * Verificar si el producto está sincronizado con Tienda Nube
     */
    public function isSyncedWithTiendaNube(): bool
    {
        return !empty($this->tiendanube_product_id);
    }

    /**
     * Verificar si el producto necesita sincronización
     */
    public function needsTiendaNubeSync(): bool
    {
        if (!$this->publicar_tiendanube) {
            return false;
        }

        if (!$this->isSyncedWithTiendaNube()) {
            return true;
        }

        // Si fue modificado después de la última sincronización
        return $this->updated_at > $this->tiendanube_synced_at;
    }

    /**
     * Scope para productos que deben publicarse en Tienda Nube
     */
    public function scopeForTiendaNube($query)
    {
        return $query->where('publicar_tiendanube', true);
    }

    /**
     * Scope para productos pendientes de sincronización
     */
    public function scopePendingTiendaNubeSync($query)
    {
        return $query->where('publicar_tiendanube', true)
            ->where(function ($q) {
                $q->whereNull('tiendanube_product_id')
                    ->orWhereColumn('updated_at', '>', 'tiendanube_synced_at')
                    ->orWhereNull('tiendanube_synced_at');
            });
    }
}
