<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicalRecord extends Model
{
    protected $fillable = [
        'client_id',
        'service_date',
        'hair_type',
        'scalp_condition',
        'current_hair_color',
        'desired_hair_color',
        'hair_treatments',
        'products_used',
        'observations',
        'photos',
        'next_appointment_notes',
        'stylist_id'
    ];

    protected $casts = [
        'products_used' => 'array',
        'photos' => 'array',
        'service_date' => 'datetime'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function stylist()
    {
        return $this->belongsTo(User::class, 'stylist_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'technical_record_product');
    }

}
