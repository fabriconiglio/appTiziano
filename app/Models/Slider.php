<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slider extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'tag',
        'cta_text',
        'cta_link',
        'image',
        'image_mobile',
        'bg_color',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    public function getImageMobileUrlAttribute(): ?string
    {
        if ($this->image_mobile) {
            return asset('storage/' . $this->image_mobile);
        }
        return null;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($slider) {
            if ($slider->image) {
                Storage::disk('public')->delete($slider->image);
            }
            if ($slider->image_mobile) {
                Storage::disk('public')->delete($slider->image_mobile);
            }
        });
    }
}
