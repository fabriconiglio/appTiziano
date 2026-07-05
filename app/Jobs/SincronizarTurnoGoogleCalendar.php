<?php

namespace App\Jobs;

use App\Models\Turno;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sincroniza un turno con Google Calendar (una vía). Acciones: crear | actualizar | eliminar.
 * El id de turno se pasa en vez del modelo para sobrevivir a borrados.
 */
class SincronizarTurnoGoogleCalendar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $turnoId;
    public string $accion;
    public ?string $googleEventId;

    public function __construct(int $turnoId, string $accion, ?string $googleEventId = null)
    {
        $this->turnoId = $turnoId;
        $this->accion = $accion;
        $this->googleEventId = $googleEventId;
    }

    public function handle(GoogleCalendarService $google): void
    {
        if (! $google->habilitado()) {
            return;
        }

        // Eliminar no necesita el turno (puede haber sido borrado).
        if ($this->accion === 'eliminar') {
            if ($this->googleEventId) {
                $google->eliminarEvento($this->googleEventId);
            }
            return;
        }

        $turno = Turno::with(['client', 'peluquera', 'servicio'])->find($this->turnoId);
        if (! $turno) {
            return;
        }

        if ($this->accion === 'crear') {
            $resultado = $google->crearEvento($turno);
            if ($resultado) {
                // google_updated_at = guard anti-loop: el polling ignora este eco.
                // OJO: convertir a la zona de la app antes de guardar (viene en UTC).
                $turno->update([
                    'google_event_id' => $resultado['id'],
                    'google_updated_at' => \Carbon\Carbon::parse($resultado['updated'])->setTimezone(config('app.timezone')),
                ]);
            }
            return;
        }

        if ($this->accion === 'actualizar') {
            $resultado = $google->actualizarEvento($turno);
            if ($resultado) {
                $turno->update([
                    'google_event_id' => $resultado['id'],
                    'google_updated_at' => \Carbon\Carbon::parse($resultado['updated'])->setTimezone(config('app.timezone')),
                ]);
            }
        }
    }
}
