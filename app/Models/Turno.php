<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    protected $table = 'turnos';

    protected $fillable = [
        'client_id',
        'cliente_nombre',
        'cliente_telefono',
        'peluquera_id',
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

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'turno_servicio');
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

        // Sin ningún dato de cliente (ni ficha ni nombre suelto) y sin color
        // propio: naranja de aviso. Un turno con nombre suelto es deliberado,
        // así que no se marca como incompleto.
        if (! $this->client_id && ! $this->cliente_nombre) {
            return '#fd7e14';
        }

        return $this->peluquera?->color
            ?? $this->servicios->first()?->color_default
            ?? '#3788d8';
    }

    /**
     * Representación del turno para FullCalendar. Requiere client/peluquera/servicios cargados.
     */
    public function aEventoCalendario(): array
    {
        $nombresServicios = $this->servicios->pluck('nombre')->implode(', ');

        // Nombre a mostrar: la ficha del cliente, o el nombre suelto de un turno
        // sin registrar, o el aviso de turno incompleto (importado de Google).
        $nombreCliente = $this->client?->full_name ?: $this->cliente_nombre;
        $titulo = $nombreCliente
            ? trim($nombreCliente . ($nombresServicios ? ' · ' . $nombresServicios : ''))
            : '⚠ Sin asignar' . ($this->notas ? ' · ' . strtok($this->notas, "\n") : '');

        return [
            'id' => $this->id,
            'title' => $titulo,
            'start' => $this->inicia_en->toIso8601String(),
            'end' => $this->termina_en->toIso8601String(),
            'color' => $this->colorCalendario(),
            'extendedProps' => [
                'estado' => $this->estado,
                'peluquera_id' => $this->peluquera_id,
                'peluquera' => $this->peluquera?->nombre,
                'servicio_ids' => $this->servicios->pluck('id')->all(),
                'servicio' => $nombresServicios,
                'client_id' => $this->client_id,
                'cliente' => $this->client?->full_name,
                'cliente_telefono' => $this->client?->phone,
                'cliente_nombre_libre' => $this->cliente_nombre,
                'cliente_telefono_libre' => $this->cliente_telefono,
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
