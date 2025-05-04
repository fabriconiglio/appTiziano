<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'module_type',
        'slug',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Esta función genera automáticamente el slug basado en el nombre
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->slug = \Str::slug($category->name);
        });
    }

    // Relación con productos (cuando los crees)
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scope para filtrar por módulo
    public function scopeByModule($query, $moduleType)
    {
        return $query->where('module_type', $moduleType);
    }

    // Scope para categorías activas
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function brands()
    {
        return $this->belongsToMany(Brand::class);
    }
}
