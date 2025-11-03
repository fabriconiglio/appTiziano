<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HairdressingSupplierCurrentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'hairdressing_supplier_id',
        'user_id',
        'hairdressing_supplier_purchase_id',
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
     * Relación con el proveedor de peluquería
     */
    public function hairdressingSupplier()
    {
        return $this->belongsTo(HairdressingSupplier::class);
    }

    /**
     * Relación con el usuario que creó el movimiento
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la compra del proveedor de peluquería
     */
    public function hairdressingSupplierPurchase()
    {
        return $this->belongsTo(HairdressingSupplierPurchase::class);
    }

    /**
     * Obtener el saldo actual de un proveedor de peluquería
     * Saldo = Deudas - Pagos - Créditos
     * Si el resultado es negativo, hay saldo a favor
     */
    public static function getCurrentBalance($hairdressingSupplierId)
    {
        $debts = self::where('hairdressing_supplier_id', $hairdressingSupplierId)
            ->where('type', 'debt')
            ->sum('amount');
        
        $payments = self::where('hairdressing_supplier_id', $hairdressingSupplierId)
            ->where('type', 'payment')
            ->sum('amount');
        
        $credits = self::where('hairdressing_supplier_id', $hairdressingSupplierId)
            ->where('type', 'credit')
            ->sum('amount');
        
        // El cálculo correcto es: Deudas - Pagos - Créditos
        // Si el resultado es negativo, hay saldo a favor
        $balance = $debts - $payments - $credits;
        
        return $balance;
    }

    /**
     * Obtener el saldo formateado de un proveedor de peluquería
     */
    public static function getFormattedBalance($hairdressingSupplierId)
    {
        $balance = self::getCurrentBalance($hairdressingSupplierId);
        
        if ($balance > 0) {
            return '$' . number_format($balance, 2) . ' (Debe)';
        } elseif ($balance < 0) {
            return '$' . number_format(abs($balance), 2) . ' (A favor)';
        } else {
            return '$0.00 (Al día)';
        }
    }

    /**
     * Verificar si un proveedor de peluquería tiene crédito (saldo a favor)
     */
    public static function hasCredit($hairdressingSupplierId)
    {
        return self::getCurrentBalance($hairdressingSupplierId) < 0;
    }

    /**
     * Obtener el crédito disponible de un proveedor de peluquería
     */
    public static function getAvailableCredit($hairdressingSupplierId)
    {
        $balance = self::getCurrentBalance($hairdressingSupplierId);
        return $balance < 0 ? abs($balance) : 0;
    }
}
