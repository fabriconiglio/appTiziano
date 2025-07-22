<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'birth_date',
        'domicilio',
        'observations',
    ];

    protected $dates = [
        'birth_date'
    ];

    protected $casts = [
        'birth_date' => 'date'
    ];

    public function technicalRecords()
    {
        return $this->hasMany(TechnicalRecord::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
