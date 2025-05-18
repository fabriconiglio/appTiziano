<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributorCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = str($category->name)->slug();
        });
    }

    public function brands()
    {
        return $this->belongsToMany(DistributorBrand::class, 'distributor_brand_category', 'distributor_category_id', 'distributor_brand_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 