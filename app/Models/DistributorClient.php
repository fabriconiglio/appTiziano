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

    public function currentAccounts()
    {
        return $this->hasMany(DistributorCurrentAccount::class);
    }

    public function quotations()
    {
        return $this->hasMany(DistributorQuotation::class);
    }

    /**
     * Obtener el saldo actual de la cuenta corriente
     */
    public function getCurrentBalance()
    {
        return DistributorCurrentAccount::getCurrentBalance($this->id);
    }

    /**
     * Obtener el saldo formateado
     */
    public function getFormattedBalance()
    {
        return DistributorCurrentAccount::getFormattedBalance($this->id);
    }

    /**
     * Verificar si tiene deuda
     */
    public function hasDebt()
    {
        return DistributorCurrentAccount::hasDebt($this->id);
    }

    /**
     * Obtener el nombre completo
     */
    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->surname;
    }
}
