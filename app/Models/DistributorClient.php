<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorClient extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'email',
        'dni',
        'phone',
        'birth_date',
        'observations'
    ];

    protected $dates = [
        'birth_date'
    ];

    protected $casts = [
        'birth_date' => 'date'
    ];
}
