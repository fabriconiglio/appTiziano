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
     * Crea el evento espejo. Devuelve ['id' => ..., 'updated' => ...] o null.
     * El 'updated' se guarda en turnos.google_updated_at (guard anti-loop del polling).
     */
    public function crearEvento(Turno $turno): ?array
    {
        if (! $this->habilitado()) {
            return null;
        }

        try {
            $evento = $this->servicio()->events->insert(
                config('services.google.calendar_id'),
                $this->construirEvento($turno)
            );

            return ['id' => $evento->getId(), 'updated' => $evento->getUpdated()];
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
     * Devuelve ['id' => ..., 'updated' => ...] o null.
     */
    public function actualizarEvento(Turno $turno): ?array
    {
        if (! $this->habilitado()) {
            return null;
        }

        if (! $turno->google_event_id) {
            return $this->crearEvento($turno);
        }

        try {
            $evento = $this->servicio()->events->update(
                config('services.google.calendar_id'),
                $turno->google_event_id,
                $this->construirEvento($turno)
            );

            return ['id' => $turno->google_event_id, 'updated' => $evento->getUpdated()];
        } catch (\Throwable $e) {
            Log::error('GoogleCalendar: no se pudo actualizar el evento', [
                'turno_id' => $turno->id,
                'google_event_id' => $turno->google_event_id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Polling incremental (sync bidireccional): trae los eventos que cambiaron en
     * Google desde el syncToken dado. Sin token hace una carga inicial acotada
     * (último mes en adelante) solo para obtener el primer token.
     *
     * Devuelve:
     *  - ok: false si hubo error de API (reintentar en la próxima corrida)
     *  - token_vencido: true si Google devolvió 410 GONE (hay que resetear el token)
     *  - eventos: lista de Google\Service\Calendar\Event cambiados
     *  - sync_token: el nuevo token a guardar para la próxima corrida
     */
    public function traerCambios(?string $syncToken): array
    {
        $resultado = ['ok' => false, 'token_vencido' => false, 'eventos' => [], 'sync_token' => null];

        if (! $this->habilitado()) {
            return $resultado;
        }

        $calendarId = config('services.google.calendar_id');
        $servicio = $this->servicio();

        $params = ['maxResults' => 250, 'showDeleted' => true];
        if ($syncToken) {
            $params['syncToken'] = $syncToken;
        } else {
            // Primera corrida: acotar al último mes para no traer todo el historial.
            // (timeMin y syncToken son excluyentes en la API.)
            $params['timeMin'] = now()->subMonth()->toRfc3339String();
        }

        try {
            $pageToken = null;
            do {
                if ($pageToken) {
                    $params['pageToken'] = $pageToken;
                }

                $respuesta = $servicio->events->listEvents($calendarId, $params);

                foreach ($respuesta->getItems() as $evento) {
                    $resultado['eventos'][] = $evento;
                }

                $pageToken = $respuesta->getNextPageToken();
                // El nextSyncToken solo viene en la última página.
                if ($respuesta->getNextSyncToken()) {
                    $resultado['sync_token'] = $respuesta->getNextSyncToken();
                }
            } while ($pageToken);

            $resultado['ok'] = true;

            return $resultado;
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 410) {
                // Token vencido: el caller debe descartar el token y re-sincronizar.
                $resultado['token_vencido'] = true;

                return $resultado;
            }

            Log::error('GoogleCalendar: error en polling de cambios', ['error' => $e->getMessage()]);

            return $resultado;
        } catch (\Throwable $e) {
            Log::error('GoogleCalendar: error en polling de cambios', ['error' => $e->getMessage()]);

            return $resultado;
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
