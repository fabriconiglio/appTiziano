<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SupplierInventory extends Model
{
    protected $fillable = [
        'product_name',
        'sku',
        'codigo_barra',
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
        'is_featured',
        'peso_gramos',
        'volumen_cm3',
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
        'is_featured' => 'boolean',
        'peso_gramos' => 'decimal:2',
        'volumen_cm3' => 'decimal:2',
    ];

    /**
     * Genera un EAN-13 interno único (a pedido, para productos sin código de fábrica).
     * Prefijo 20–29: rango que GS1 reserva para uso interno de comercios, así no choca
     * con códigos reales. Devuelve 13 dígitos válidos (con dígito verificador).
     */
    public static function generarCodigoBarraUnico(): string
    {
        do {
            // '20' + 10 dígitos aleatorios = 12 dígitos; el 13º es el verificador.
            $base = '20' . str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
            $codigo = $base . self::digitoVerificadorEan13($base);
        } while (static::where('codigo_barra', $codigo)->exists());

        return $codigo;
    }

    /**
     * Dígito verificador EAN-13 a partir de los primeros 12 dígitos.
     */
    public static function digitoVerificadorEan13(string $doce): int
    {
        $suma = 0;
        for ($i = 0; $i < 12; $i++) {
            $suma += (int) $doce[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return (10 - ($suma % 10)) % 10;
    }

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

    protected static function booted(): void
    {
        static::created(function (SupplierInventory $item) {
            if (empty($item->slug)) {
                $item->slug = static::uniqueSlugForProduct($item->product_name, $item->id);
                $item->saveQuietly();
            }
        });

        static::saving(function (SupplierInventory $item) {
            if (! $item->exists) {
                return;
            }
            if ($item->isDirty('product_name')) {
                $item->slug = static::uniqueSlugForProduct($item->product_name, $item->id);
            }
        });
    }

    public static function uniqueSlugForProduct(string $name, int $id): string
    {
        $base = Str::slug($name) ?: 'producto';
        $slug = $base.'-'.$id;
        $candidate = $slug;
        $n = 2;
        while (static::where('slug', $candidate)->where('id', '!=', $id)->exists()) {
            $candidate = $slug.'-'.$n++;
        }

        return $candidate;
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

}
