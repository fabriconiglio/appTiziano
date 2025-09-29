<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientCurrentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'technical_record_id',
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

    /**
     * Relación con el cliente
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación con el usuario que creó el movimiento
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la ficha técnica
     */
    public function technicalRecord()
    {
        return $this->belongsTo(TechnicalRecord::class);
    }

    /**
     * Obtener el saldo actual de un cliente
     */
    public static function getCurrentBalance($clientId)
    {
        $debts = self::where('client_id', $clientId)
            ->where('type', 'debt')
            ->sum('amount');
        
        $payments = self::where('client_id', $clientId)
            ->where('type', 'payment')
            ->sum('amount');
        
        return $debts - $payments;
    }

    /**
     * Obtener el saldo formateado de un cliente
     */
    public static function getFormattedBalance($clientId)
    {
        $balance = self::getCurrentBalance($clientId);
        
        if ($balance > 0) {
            return '$' . number_format($balance, 2) . ' (Debe)';
        } elseif ($balance < 0) {
            return '$' . number_format(abs($balance), 2) . ' (A favor)';
        } else {
            return '$0.00 (Al día)';
        }
    }

    /**
     * Verificar si un cliente tiene deuda
     */
    public static function hasDebt($clientId)
    {
        return self::getCurrentBalance($clientId) > 0;
    }
} 