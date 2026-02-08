<?php

namespace App\Models;

use App\Models\Client;
use App\Models\ClienteNoFrecuente;
use App\Models\DistributorClient;
use App\Models\DistributorClienteNoFrecuente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AfipInvoice extends Model
{
    protected $fillable = [
        'distributor_client_id',
        'client_type',
        'client_id',
        'technical_record_id',
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

    public function technicalRecord(): BelongsTo
    {
        return $this->belongsTo(DistributorTechnicalRecord::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AfipInvoiceItem::class);
    }

    /**
     * Obtener el cliente según su tipo
     */
    public function getClient()
    {
        // Consumidor Final no tiene cliente asociado
        if ($this->client_type === 'consumidor_final') {
            return null;
        }

        if (!$this->client_type || !$this->client_id) {
            // Compatibilidad hacia atrás: usar distributorClient si existe
            return $this->distributorClient;
        }

        switch ($this->client_type) {
            case 'distributor_client':
                return DistributorClient::find($this->client_id);
            case 'client':
                return Client::find($this->client_id);
            case 'distributor_no_frecuente':
                return DistributorClienteNoFrecuente::find($this->client_id);
            case 'client_no_frecuente':
                return ClienteNoFrecuente::find($this->client_id);
            default:
                return null;
        }
    }

    /**
     * Verificar si es Consumidor Final
     */
    public function isConsumidorFinal(): bool
    {
        return $this->client_type === 'consumidor_final';
    }

    /**
     * Obtener el nombre completo del cliente
     */
    public function getClientFullNameAttribute(): string
    {
        if ($this->client_type === 'consumidor_final') {
            return 'Consumidor Final';
        }

        $client = $this->getClient();
        
        if (!$client) {
            return 'Cliente no disponible';
        }

        switch ($this->client_type) {
            case 'distributor_client':
            case 'client':
                return $client->full_name ?? ($client->name . ' ' . $client->surname);
            case 'distributor_no_frecuente':
            case 'client_no_frecuente':
                return $client->nombre ?? 'Sin nombre';
            default:
                return 'Cliente no disponible';
        }
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
