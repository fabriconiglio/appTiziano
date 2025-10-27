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
        'barrio',
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
     * Obtener el saldo actual de la cuenta corriente (como atributo)
     */
    public function getCurrentBalanceAttribute()
    {
        return ClientCurrentAccount::getCurrentBalance($this->id);
    }

    /**
     * Obtener el saldo formateado de la cuenta corriente (como atributo)
     */
    public function getFormattedBalanceAttribute()
    {
        return ClientCurrentAccount::getFormattedBalance($this->id);
    }

    /**
     * Verificar si tiene deuda (como atributo)
     */
    public function getHasDebtAttribute()
    {
        return ClientCurrentAccount::hasDebt($this->id);
    }

    /**
     * MOD-030 (main): Obtener el saldo actual de la cuenta corriente (como método)
     */
    public function getCurrentBalance()
    {
        return ClientCurrentAccount::getCurrentBalance($this->id);
    }

    /**
     * MOD-030 (main): Obtener el saldo formateado de la cuenta corriente (como método)
     */
    public function getFormattedBalance()
    {
        return ClientCurrentAccount::getFormattedBalance($this->id);
    }

    /**
     * MOD-030 (main): Verificar si tiene deuda (como método)
     */
    public function hasDebt()
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
