<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $table = 'turnos';

    protected $fillable = [
        'client_id',
        'peluquera_id',
        'servicio_id',
        'inicia_en',
        'termina_en',
        'estado',
        'color',
        'notas',
        'google_event_id',
        'google_updated_at',
        'origen',
    ];

    protected $casts = [
        'inicia_en' => 'datetime',
        'termina_en' => 'datetime',
        'google_updated_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function peluquera()
    {
        return $this->belongsTo(Peluquera::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function recordatorios()
    {
        return $this->hasMany(RecordatorioLog::class);
    }

    /**
     * Color para el calendario: el propio del turno, o el de la peluquera, o el del servicio.
     */
    public function colorCalendario(): string
    {
        if ($this->estado === 'cancelado') {
            return '#adb5bd';
        }

        // El color elegido en el sistema, o el importado del evento de Google,
        // siempre gana (aunque el turno esté sin cliente asignado).
        if ($this->color) {
            return $this->color;
        }

        // Sin cliente asignado y sin color propio: naranja de aviso por defecto.
        if (! $this->client_id) {
            return '#fd7e14';
        }

        return $this->peluquera?->color
            ?? $this->servicio?->color_default
            ?? '#3788d8';
    }

    /**
     * Representación del turno para FullCalendar. Requiere client/peluquera/servicio cargados.
     */
    public function aEventoCalendario(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->client_id
                ? trim(($this->client?->full_name ?? 'Cliente')
                    . ($this->servicio ? ' · ' . $this->servicio->nombre : ''))
                : '⚠ Sin asignar' . ($this->notas ? ' · ' . strtok($this->notas, "\n") : ''),
            'start' => $this->inicia_en->toIso8601String(),
            'end' => $this->termina_en->toIso8601String(),
            'color' => $this->colorCalendario(),
            'extendedProps' => [
                'estado' => $this->estado,
                'peluquera_id' => $this->peluquera_id,
                'peluquera' => $this->peluquera?->nombre,
                'servicio_id' => $this->servicio_id,
                'servicio' => $this->servicio?->nombre,
                'client_id' => $this->client_id,
                'cliente' => $this->client?->full_name,
                'cliente_telefono' => $this->client?->phone,
                'color_propio' => $this->color,
                'notas' => $this->notas,
            ],
        ];
    }

    /**
     * Detecta si un rango se solapa con otro turno (no cancelado) de la misma peluquera.
     */
    public static function haySolapamiento(int $peluqueraId, $iniciaEn, $terminaEn, ?int $ignorarTurnoId = null): bool
    {
        return static::query()
            ->where('peluquera_id', $peluqueraId)
            ->where('estado', '!=', 'cancelado')
            ->when($ignorarTurnoId, fn ($q) => $q->where('id', '!=', $ignorarTurnoId))
            ->where('inicia_en', '<', $terminaEn)
            ->where('termina_en', '>', $iniciaEn)
            ->exists();
    }
}
