<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AfipInvoice extends Model
{
    protected $fillable = [
        'distributor_client_id',
        'invoice_type',
        'point_of_sale',
        'invoice_number',
        'invoice_date',
        'subtotal',
        'tax_amount',
        'total',
        'cae',
        'cae_expiration',
        'status',
        'afip_response',
        'notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'cae_expiration' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'afip_response' => 'array'
    ];

    // Relaciones
    public function distributorClient(): BelongsTo
    {
        return $this->belongsTo(DistributorClient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AfipInvoiceItem::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('invoice_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    // Métodos auxiliares
    public function isAuthorized(): bool
    {
        return $this->status === 'authorized';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    public function getFormattedNumberAttribute(): string
    {
        return sprintf('%s-%s-%s', 
            $this->invoice_type, 
            str_pad($this->point_of_sale, 4, '0', STR_PAD_LEFT),
            str_pad($this->invoice_number, 8, '0', STR_PAD_LEFT)
        );
    }
}
