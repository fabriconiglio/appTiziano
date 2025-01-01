<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'email',
        'phone',
        'birth_date',
        'allergies',
        'observations'
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
