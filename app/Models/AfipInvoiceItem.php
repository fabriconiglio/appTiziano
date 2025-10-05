<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfipInvoiceItem extends Model
{
    protected $fillable = [
        'afip_invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    // Relaciones
    public function afipInvoice(): BelongsTo
    {
        return $this->belongsTo(AfipInvoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SupplierInventory::class, 'product_id');
    }

    // Métodos auxiliares
    public function calculateSubtotal(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function calculateTaxAmount(): float
    {
        return $this->subtotal * ($this->tax_rate / 100);
    }

    public function calculateTotal(): float
    {
        return $this->subtotal + $this->tax_amount;
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->subtotal = $item->calculateSubtotal();
            $item->tax_amount = $item->calculateTaxAmount();
            $item->total = $item->calculateTotal();
        });
    }
}
