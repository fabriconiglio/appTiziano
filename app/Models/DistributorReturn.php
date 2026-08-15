<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Devolución de mercadería de la distribuidora (nota de crédito interna).
 *
 * Sale siempre de una compra concreta y admite una sola devolución vigente por
 * compra. Los precios se toman de la compra original, no de la lista de hoy.
 */
class DistributorReturn extends Model
{
    public const DESTINO_CUENTA = 'cuenta_corriente';
    public const DESTINO_EFECTIVO = 'efectivo';
    public const DESTINO_VALE = 'vale';

    /** Días dentro de los que se puede devolver sin autorización. */
    public const DIAS_PLAZO = 15;

    protected $fillable = [
        'return_number',
        'distributor_client_id',
        'distributor_cliente_no_frecuente_id',
        'distributor_technical_record_id',
        'origen',
        'return_date',
        'products_returned',
        'total_amount',
        'destino',
        'motivo',
        'dias_desde_compra',
        'fuera_de_plazo',
        'autorizado_por',
        'user_id',
        'anulada_en',
        'anulada_por',
        'motivo_anulacion',
        'observations',
    ];

    protected $casts = [
        'return_date' => 'date',
        'products_returned' => 'array',
        'total_amount' => 'decimal:2',
        'fuera_de_plazo' => 'boolean',
        'anulada_en' => 'datetime',
    ];

    public function distributorClient()
    {
        return $this->belongsTo(DistributorClient::class);
    }

    public function clienteNoFrecuente()
    {
        return $this->belongsTo(DistributorClienteNoFrecuente::class, 'distributor_cliente_no_frecuente_id');
    }

    public function technicalRecord()
    {
        return $this->belongsTo(DistributorTechnicalRecord::class, 'distributor_technical_record_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anuladaPor()
    {
        return $this->belongsTo(User::class, 'anulada_por');
    }

    public function autorizadaPor()
    {
        return $this->belongsTo(User::class, 'autorizado_por');
    }

    public function movimientos()
    {
        return $this->hasMany(DistributorCurrentAccount::class, 'distributor_return_id');
    }

    public function estaAnulada(): bool
    {
        return $this->anulada_en !== null;
    }

    /** Nombre del cliente, venga de ficha o de cliente suelto. */
    public function nombreCliente(): string
    {
        return $this->distributorClient?->name
            ?? $this->clienteNoFrecuente?->nombre
            ?? 'Sin cliente';
    }

    /**
     * Siguiente número de devolución (DEV-0001).
     */
    public static function siguienteNumero(): string
    {
        $ultimo = static::orderByDesc('id')->value('return_number');
        $numero = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;

        return 'DEV-' . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Si una compra ya tiene una devolución vigente (las anuladas no cuentan).
     */
    public static function compraYaDevuelta(int $technicalRecordId): bool
    {
        return static::where('distributor_technical_record_id', $technicalRecordId)
            ->whereNull('anulada_en')
            ->exists();
    }
}
