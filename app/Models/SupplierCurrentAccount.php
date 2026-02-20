<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierCurrentAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'supplier_purchase_id',
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
     * Relación con el proveedor
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relación con el usuario que creó el movimiento
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la compra del proveedor
     */
    public function supplierPurchase()
    {
        return $this->belongsTo(SupplierPurchase::class);
    }

    /**
     * Obtener el total de débitos (importes de facturas/compras) de un proveedor
     */
    public static function getTotalDebts($supplierId)
    {
        return self::where('supplier_id', $supplierId)
            ->where('type', 'debt')
            ->sum('amount');
    }

    /**
     * Obtener el total de pagos realizados a un proveedor
     */
    public static function getTotalPayments($supplierId)
    {
        return self::where('supplier_id', $supplierId)
            ->where('type', 'payment')
            ->sum('amount');
    }

    /**
     * Obtener el total de créditos/excedentes de un proveedor
     */
    public static function getTotalCredits($supplierId)
    {
        return self::where('supplier_id', $supplierId)
            ->where('type', 'credit')
            ->sum('amount');
    }

    /**
     * Obtener el saldo actual de un proveedor
     * Saldo = Importes Facturas - Pagos - Excedentes
     * Si el resultado es negativo, hay excedente/saldo a favor
     */
    public static function getCurrentBalance($supplierId)
    {
        $debts = self::getTotalDebts($supplierId);
        $payments = self::getTotalPayments($supplierId);
        $credits = self::getTotalCredits($supplierId);
        
        // Importes Facturas - Pagos - Excedentes
        $balance = $debts - $payments - $credits;
        
        return $balance;
    }

    /**
     * Obtener el saldo formateado de un proveedor
     */
    public static function getFormattedBalance($supplierId)
    {
        $balance = self::getCurrentBalance($supplierId);
        
        if ($balance > 0) {
            return '$' . number_format($balance, 2) . ' (Debe)';
        } elseif ($balance < 0) {
            return '$' . number_format(abs($balance), 2) . ' (A favor)';
        } else {
            return '$0.00 (Al día)';
        }
    }

    /**
     * Verificar si un proveedor tiene crédito (saldo a favor)
     */
    public static function hasCredit($supplierId)
    {
        return self::getCurrentBalance($supplierId) < 0;
    }

    /**
     * Obtener el crédito disponible de un proveedor
     */
    public static function getAvailableCredit($supplierId)
    {
        $balance = self::getCurrentBalance($supplierId);
        return $balance < 0 ? abs($balance) : 0;
    }
}
