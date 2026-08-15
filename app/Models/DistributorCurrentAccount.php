<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorCurrentAccount extends Model
{
    protected $fillable = [
        'distributor_client_id',
        'user_id',
        'distributor_technical_record_id',
        'distributor_return_id',
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

    public function distributorReturn()
    {
        return $this->belongsTo(DistributorReturn::class, 'distributor_return_id');
    }

    /**
     * Obtener el saldo actual de un cliente distribuidor.
     *
     * Las notas de crédito por devolución restan igual que un pago. Si el cliente
     * devolvió más de lo que debía el saldo queda negativo: es saldo a su favor.
     */
    public static function getCurrentBalance($distributorClientId)
    {
        $debts = self::where('distributor_client_id', $distributorClientId)
            ->where('type', 'debt')
            ->sum('amount');

        $resta = self::where('distributor_client_id', $distributorClientId)
            ->whereIn('type', ['payment', 'credit'])
            ->sum('amount');

        return $debts - $resta;
    }

    /**
     * Devolución acumulada del cliente (sólo notas de crédito).
     */
    public static function getTotalCredits($distributorClientId)
    {
        return self::where('distributor_client_id', $distributorClientId)
            ->where('type', 'credit')
            ->sum('amount');
    }

    /**
     * Saldo a favor del cliente, en positivo. 0 si debe plata.
     */
    public static function getSaldoAFavor($distributorClientId)
    {
        return max(0, -self::getCurrentBalance($distributorClientId));
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
