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
     * Obtener el saldo actual de un proveedor
     * Saldo = Deudas - Pagos - Créditos
     * Si el resultado es negativo, hay saldo a favor
     */
    public static function getCurrentBalance($supplierId)
    {
        $debts = self::where('supplier_id', $supplierId)
            ->where('type', 'debt')
            ->sum('amount');
        
        $payments = self::where('supplier_id', $supplierId)
            ->where('type', 'payment')
            ->sum('amount');
        
        $credits = self::where('supplier_id', $supplierId)
            ->where('type', 'credit')
            ->sum('amount');
        
        // El cálculo correcto es: Deudas - Pagos - Créditos
        // Si el resultado es negativo, hay saldo a favor
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
