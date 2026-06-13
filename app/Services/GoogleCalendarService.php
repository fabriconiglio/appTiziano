<?php

namespace App\Services;

use App\Models\Turno;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

/**
 * Sincronización de una vía: turnos del panel -> Google Calendar del negocio.
 *
 * Autenticación por Service Account (JSON en storage/app/google/). El calendario
 * del negocio debe estar compartido con el email de la service account con permiso
 * de edición. Si la sync está deshabilitada o falta el JSON, todos los métodos son
 * no-op para no romper el alta/edición de turnos.
 */
class GoogleCalendarService
{
    private const ZONA = 'America/Argentina/Buenos_Aires';

    public function habilitado(): bool
    {
        return (bool) config('services.google.calendar_sync_enabled')
            && config('services.google.calendar_id')
            && is_file((string) config('services.google.service_account'));
    }

    /**
     * Crea el evento espejo y devuelve su id de Google (o null si no se sincronizó).
     */
    public function crearEvento(Turno $turno): ?string
    {
        if (! $this->habilitado()) {
            return null;
        }

        try {
            $evento = $this->servicio()->events->insert(
                config('services.google.calendar_id'),
                $this->construirEvento($turno)
            );

            return $evento->getId();
        } catch (\Throwable $e) {
            Log::error('GoogleCalendar: no se pudo crear el evento', [
                'turno_id' => $turno->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Actualiza el evento espejo. Si el turno aún no tenía evento, lo crea.
     */
    public function actualizarEvento(Turno $turno): ?string
    {
        if (! $this->habilitado()) {
            return null;
        }

        if (! $turno->google_event_id) {
            return $this->crearEvento($turno);
        }

        try {
            $this->servicio()->events->update(
                config('services.google.calendar_id'),
                $turno->google_event_id,
                $this->construirEvento($turno)
            );

            return $turno->google_event_id;
        } catch (\Throwable $e) {
            Log::error('GoogleCalendar: no se pudo actualizar el evento', [
                'turno_id' => $turno->id,
                'google_event_id' => $turno->google_event_id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function eliminarEvento(string $googleEventId): void
    {
        if (! $this->habilitado()) {
            return;
        }

        try {
            $this->servicio()->events->delete(config('services.google.calendar_id'), $googleEventId);
        } catch (\Throwable $e) {
            Log::warning('GoogleCalendar: no se pudo eliminar el evento', [
                'google_event_id' => $googleEventId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function servicio(): Calendar
    {
        $client = new GoogleClient();
        $client->setAuthConfig((string) config('services.google.service_account'));
        $client->addScope(Calendar::CALENDAR_EVENTS);

        return new Calendar($client);
    }

    private function construirEvento(Turno $turno): Event
    {
        $turno->loadMissing(['client', 'peluquera', 'servicio']);

        $cliente = $turno->client->full_name ?? 'Cliente';
        $servicio = $turno->servicio->nombre ?? 'Servicio';
        $peluquera = $turno->peluquera->nombre ?? '';

        $descripcion = "Servicio: {$servicio}\nPeluquera: {$peluquera}\nEstado: {$turno->estado}";
        if ($turno->client?->phone) {
            $descripcion .= "\nTel: {$turno->client->phone}";
        }
        if ($turno->notas) {
            $descripcion .= "\nNotas: {$turno->notas}";
        }

        return new Event([
            'summary' => "{$cliente} · {$servicio}",
            'description' => $descripcion,
            'start' => new EventDateTime([
                'dateTime' => $turno->inicia_en->toRfc3339String(),
                'timeZone' => self::ZONA,
            ]),
            'end' => new EventDateTime([
                'dateTime' => $turno->termina_en->toRfc3339String(),
                'timeZone' => self::ZONA,
            ]),
        ]);
    }
}
