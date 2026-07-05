<?php

namespace App\Console\Commands;

use App\Models\GoogleCalendarSync;
use App\Models\Turno;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Google\Service\Calendar\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Sync bidireccional (vía Google -> sistema): trae los cambios del calendario
 * con polling incremental (syncToken) y los aplica a los turnos existentes.
 *
 * Alcance: SOLO eventos que corresponden a turnos conocidos (match por
 * google_event_id). Mover el evento actualiza el horario del turno; borrarlo
 * lo cancela. Eventos ajenos o creados a mano en Google se ignoran.
 */
class SincronizarAgendaDesdeGoogle extends Command
{
    protected $signature = 'agenda:sync-google';

    protected $description = 'Aplica a los turnos los cambios hechos en Google Calendar (sync bidireccional)';

    public function handle(GoogleCalendarService $google): int
    {
        if (! $google->habilitado()) {
            $this->info('Sync con Google Calendar deshabilitada (GOOGLE_CALENDAR_SYNC=false o falta config).');

            return 0;
        }

        $estado = GoogleCalendarSync::firstOrCreate([
            'calendar_id' => config('services.google.calendar_id'),
        ]);

        $resultado = $google->traerCambios($estado->sync_token);

        // Token vencido (410): descartarlo y re-sincronizar completo en el acto.
        if ($resultado['token_vencido']) {
            $this->warn('syncToken vencido: re-sincronizando desde cero.');
            $estado->update(['sync_token' => null]);
            $resultado = $google->traerCambios(null);
        }

        if (! $resultado['ok']) {
            $this->error('No se pudieron traer los cambios de Google (ver logs).');

            return 1;
        }

        $aplicados = 0;
        foreach ($resultado['eventos'] as $evento) {
            if ($this->procesarEvento($evento)) {
                $aplicados++;
            }
        }

        $estado->update([
            'sync_token' => $resultado['sync_token'] ?? $estado->sync_token,
            'ultima_sync_en' => now(),
        ]);

        $recibidos = count($resultado['eventos']);
        $this->info("Sync OK: {$recibidos} eventos recibidos, {$aplicados} turnos actualizados.");

        return 0;
    }

    /**
     * Aplica un evento cambiado de Google al turno correspondiente.
     * Devuelve true si modificó algo en el turno.
     */
    private function procesarEvento(Event $evento): bool
    {
        $turno = Turno::where('google_event_id', $evento->getId())->first();

        // Evento sin turno vinculado: si el título empieza con "turno", se importa
        // como turno sin asignar. El resto (cumpleaños, eventos personales) se ignora.
        if (! $turno) {
            return $this->importarEventoNuevo($evento);
        }

        // Evento borrado en Google -> cancelar el turno (conserva historial).
        if ($evento->getStatus() === 'cancelled') {
            if ($turno->estado === 'cancelado') {
                return false; // ya estaba cancelado (ej: lo canceló el propio sistema)
            }

            $turno->update([
                'estado' => 'cancelado',
                'google_updated_at' => $evento->getUpdated()
                    ? Carbon::parse($evento->getUpdated())->setTimezone(config('app.timezone'))
                    : now(),
            ]);

            Log::info('SyncGoogle: turno cancelado desde Google Calendar', ['turno_id' => $turno->id]);

            return true;
        }

        // Guard anti-loop + "gana el más nuevo": si el updated del evento no es
        // posterior al último que procesamos/empujamos, es un eco o un cambio viejo.
        // Convertido a la zona de la app para comparar/guardar consistente.
        $updatedGoogle = Carbon::parse($evento->getUpdated())->setTimezone(config('app.timezone'));
        if ($turno->google_updated_at && $updatedGoogle->lte($turno->google_updated_at)) {
            return false;
        }

        // Solo eventos con hora (dateTime). Un evento "de todo el día" no es un turno válido.
        $inicio = $evento->getStart()?->getDateTime();
        $fin = $evento->getEnd()?->getDateTime();
        if (! $inicio || ! $fin) {
            $turno->update(['google_updated_at' => $updatedGoogle]);

            return false;
        }

        $iniciaEn = Carbon::parse($inicio)->setTimezone(config('app.timezone'));
        $terminaEn = Carbon::parse($fin)->setTimezone(config('app.timezone'));

        $cambioHorario = ! $turno->inicia_en->equalTo($iniciaEn) || ! $turno->termina_en->equalTo($terminaEn);

        // Marcar el updated como procesado siempre (aunque solo hayan tocado el
        // título/descripción, que no mapean a nada del turno).
        $turno->google_updated_at = $updatedGoogle;

        if ($cambioHorario) {
            $turno->inicia_en = $iniciaEn;
            $turno->termina_en = $terminaEn;

            Log::info('SyncGoogle: turno reagendado desde Google Calendar', [
                'turno_id' => $turno->id,
                'inicia_en' => $iniciaEn->toDateTimeString(),
                'termina_en' => $terminaEn->toDateTimeString(),
            ]);
        }

        $turno->save();

        return $cambioHorario;
    }

    /**
     * Importa un evento creado a mano en Google Calendar como "turno sin asignar".
     * Solo si el título empieza con "turno" (filtro para no importar cumpleaños ni
     * eventos personales del calendario), tiene hora (no es de día completo) y es
     * a futuro. El cliente se completa después desde el panel.
     */
    private function importarEventoNuevo(Event $evento): bool
    {
        if ($evento->getStatus() === 'cancelled') {
            return false;
        }

        $titulo = trim((string) $evento->getSummary());
        if (! preg_match('/^turno/i', $titulo)) {
            return false;
        }

        // Solo eventos con hora concreta (los de "todo el día" no son turnos).
        $inicio = $evento->getStart()?->getDateTime();
        $fin = $evento->getEnd()?->getDateTime();
        if (! $inicio || ! $fin) {
            return false;
        }

        $iniciaEn = Carbon::parse($inicio)->setTimezone(config('app.timezone'));
        $terminaEn = Carbon::parse($fin)->setTimezone(config('app.timezone'));

        // No importar eventos pasados (ej: en un resync completo del último mes).
        if ($terminaEn->isPast()) {
            return false;
        }

        $notas = $titulo;
        if ($evento->getDescription()) {
            $notas .= "\n" . trim($evento->getDescription());
        }

        $turno = Turno::create([
            'client_id' => null,
            'inicia_en' => $iniciaEn,
            'termina_en' => $terminaEn,
            'estado' => 'pendiente',
            'notas' => $notas,
            'origen' => 'google',
            'google_event_id' => $evento->getId(),
            'google_updated_at' => Carbon::parse($evento->getUpdated())->setTimezone(config('app.timezone')),
        ]);

        Log::info('SyncGoogle: turno importado desde Google Calendar (sin asignar)', [
            'turno_id' => $turno->id,
            'titulo' => $titulo,
            'inicia_en' => $iniciaEn->toDateTimeString(),
        ]);

        return true;
    }
}
