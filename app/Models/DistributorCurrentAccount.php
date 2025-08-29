<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorCurrentAccount extends Model
{
    protected $fillable = [
        'distributor_client_id',
        'user_id',
        'distributor_technical_record_id',
        'type',
        'amount',
        'description',
        'date',
        'reference',
        'observations'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date'
    ];

    public function distributorClient()
    {
        return $this->belongsTo(DistributorClient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function distributorTechnicalRecord()
    {
        return $this->belongsTo(DistributorTechnicalRecord::class);
    }

    /**
     * Obtener el saldo actual de un cliente distribuidor
     */
    public static function getCurrentBalance($distributorClientId)
    {
        $debts = self::where('distributor_client_id', $distributorClientId)
            ->where('type', 'debt')
            ->sum('amount');
        
        $payments = self::where('distributor_client_id', $distributorClientId)
            ->where('type', 'payment')
            ->sum('amount');
        
        return $debts - $payments;
    }

    /**
     * Obtener el saldo formateado
     */
    public static function getFormattedBalance($distributorClientId)
    {
        $balance = self::getCurrentBalance($distributorClientId);
        return number_format($balance, 2, ',', '.');
    }

    /**
     * Verificar si el cliente tiene deuda
     */
    public static function hasDebt($distributorClientId)
    {
        return self::getCurrentBalance($distributorClientId) > 0;
    }
}
