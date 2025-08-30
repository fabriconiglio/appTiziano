<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HairdressingSupplierPurchase extends Model
{
    protected $fillable = [
        'hairdressing_supplier_id',
        'user_id',
        'purchase_date',
        'receipt_number',
        'total_amount',
        'payment_amount',
        'balance_amount',
        'receipt_file',
        'notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2'
    ];

    /**
     * Obtener el proveedor de peluquería asociado a esta compra
     */
    public function hairdressingSupplier(): BelongsTo
    {
        return $this->belongsTo(HairdressingSupplier::class);
    }

    /**
     * Obtener el usuario que registró esta compra
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el saldo pendiente calculado
     */
    public function getCalculatedBalanceAttribute(): float
    {
        return $this->total_amount - $this->payment_amount;
    }
}
