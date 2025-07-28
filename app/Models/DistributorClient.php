<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributorClient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'dni',
        'phone',
        'birth_date',
        'domicilio',
        'observations'
    ];

    protected $dates = [
        'birth_date'
    ];

    protected $casts = [
        'birth_date' => 'date'
    ];

    public function distributorTechnicalRecords()
    {
        return $this->hasMany(DistributorTechnicalRecord::class);
    }
}
