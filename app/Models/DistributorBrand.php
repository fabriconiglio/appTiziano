<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributorBrand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_url',
        'is_active'
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(DistributorCategory::class, 'distributor_brand_category', 'distributor_brand_id', 'distributor_category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'distributor_brand_id');
    }
}
