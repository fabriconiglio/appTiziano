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
        'dni',
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



    public function currentAccounts()
    {
        return $this->hasMany(ClientCurrentAccount::class);
    }

    /**
     * Obtener el saldo actual de la cuenta corriente
     */
    public function getCurrentBalanceAttribute()
    {
        return ClientCurrentAccount::getCurrentBalance($this->id);
    }

    /**
     * Obtener el saldo formateado de la cuenta corriente
     */
    public function getFormattedBalanceAttribute()
    {
        return ClientCurrentAccount::getFormattedBalance($this->id);
    }

    /**
     * Verificar si tiene deuda
     */
    public function getHasDebtAttribute()
    {
        return ClientCurrentAccount::hasDebt($this->id);
    }

    /**
     * Obtener el nombre completo
     */
    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->surname;
    }
}
