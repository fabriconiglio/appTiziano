<?php

namespace App\Http\Controllers;

use App\Jobs\SincronizarTurnoGoogleCalendar;
use App\Models\Client;
use App\Models\Servicio;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    /**
     * Crear un turno nuevo (desde el modal del calendario, vía AJAX).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'cliente_nombre' => 'nullable|string|max:255|required_without:client_id',
            'cliente_telefono' => 'nullable|string|max:30',
            'peluquera_id' => 'nullable|exists:peluqueras,id',
            'servicio_ids' => 'nullable|array',
            'servicio_ids.*' => 'integer|exists:servicios,id',
            'inicia_en' => 'required|date',
            'estado' => 'nullable|in:pendiente,confirmado,cancelado',
            'color' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'notas' => 'nullable|string',
        ], [
            'cliente_nombre.required_without' => 'Elegí un cliente o escribí un nombre para agendar sin registrar.',
        ]);

        $servicioIds = $validated['servicio_ids'] ?? [];
        $iniciaEn = Carbon::parse($validated['inicia_en']);
        $terminaEn = (clone $iniciaEn)->addMinutes($this->duracion($servicioIds));

        if (! empty($validated['peluquera_id'])
            && Turno::haySolapamiento($validated['peluquera_id'], $iniciaEn, $terminaEn)) {
            return response()->json([
                'message' => 'La peluquera ya tiene un turno en ese horario.',
            ], 422);
        }

        // Con cliente registrado el nombre suelto no se usa (manda la ficha).
        $sinRegistrar = empty($validated['client_id']);

        $turno = Turno::create([
            'client_id' => $validated['client_id'] ?? null,
            'cliente_nombre' => $sinRegistrar ? trim($validated['cliente_nombre']) : null,
            'cliente_telefono' => $sinRegistrar ? ($validated['cliente_telefono'] ?? null) : null,
            'peluquera_id' => $validated['peluquera_id'] ?? null,
            'inicia_en' => $iniciaEn,
            'termina_en' => $terminaEn,
            'estado' => $validated['estado'] ?? 'pendiente',
            'color' => $validated['color'] ?? null,
            'notas' => $validated['notas'] ?? null,
        ]);

        $turno->servicios()->sync($servicioIds);

        if (! $sinRegistrar) {
            $this->actualizarTelefonoCliente($validated['client_id'], $validated['telefono'] ?? null);
        }

        SincronizarTurnoGoogleCalendar::dispatch($turno->id, 'crear');

        return response()->json([
            'message' => 'Turno creado correctamente.',
            'turno' => $turno->load(['client', 'peluquera', 'servicios'])->aEventoCalendario(),
        ], 201);
    }

    /**
     * Editar un turno existente.
     */
    public function update(Request $request, Turno $turno): JsonResponse
    {
        // A diferencia del alta, acá no se exige cliente: los turnos importados de
        // Google Calendar todavía no lo tienen y hay que poder editarlos igual.
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'cliente_nombre' => 'nullable|string|max:255',
            'cliente_telefono' => 'nullable|string|max:30',
            'peluquera_id' => 'nullable|exists:peluqueras,id',
            'servicio_ids' => 'nullable|array',
            'servicio_ids.*' => 'integer|exists:servicios,id',
            'inicia_en' => 'required|date',
            'estado' => 'required|in:pendiente,confirmado,cancelado',
            'color' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'notas' => 'nullable|string',
        ]);

        $servicioIds = $validated['servicio_ids'] ?? [];
        $iniciaEn = Carbon::parse($validated['inicia_en']);
        $terminaEn = (clone $iniciaEn)->addMinutes($this->duracion($servicioIds));

        if (! empty($validated['peluquera_id'])
            && Turno::haySolapamiento($validated['peluquera_id'], $iniciaEn, $terminaEn, $turno->id)) {
            return response()->json([
                'message' => 'La peluquera ya tiene un turno en ese horario.',
            ], 422);
        }

        // Al asignarle una ficha de cliente, se limpian los datos sueltos.
        $sinRegistrar = empty($validated['client_id']);
        $nombreLibre = trim((string) ($validated['cliente_nombre'] ?? ''));

        $turno->update([
            'client_id' => $validated['client_id'] ?? null,
            'cliente_nombre' => $sinRegistrar ? ($nombreLibre ?: null) : null,
            'cliente_telefono' => $sinRegistrar ? ($validated['cliente_telefono'] ?? null) : null,
            'peluquera_id' => $validated['peluquera_id'] ?? null,
            'inicia_en' => $iniciaEn,
            'termina_en' => $terminaEn,
            'estado' => $validated['estado'],
            'color' => $validated['color'] ?? null,
            'notas' => $validated['notas'] ?? null,
        ]);

        $turno->servicios()->sync($servicioIds);

        if (! $sinRegistrar) {
            $this->actualizarTelefonoCliente($validated['client_id'], $validated['telefono'] ?? null);
        }

        SincronizarTurnoGoogleCalendar::dispatch($turno->id, 'actualizar');

        return response()->json([
            'message' => 'Turno actualizado correctamente.',
            'turno' => $turno->load(['client', 'peluquera', 'servicios'])->aEventoCalendario(),
        ]);
    }

    /**
     * Reagendar por drag & drop: solo mueve inicio (mantiene servicio/duración).
     */
    public function reagendar(Request $request, Turno $turno): JsonResponse
    {
        $validated = $request->validate([
            'inicia_en' => 'required|date',
        ]);

        $iniciaEn = Carbon::parse($validated['inicia_en']);
        $duracion = $turno->servicios->sum('duracion_minutos') ?: $turno->inicia_en->diffInMinutes($turno->termina_en);
        $terminaEn = (clone $iniciaEn)->addMinutes($duracion ?: 30);

        if ($turno->peluquera_id
            && Turno::haySolapamiento($turno->peluquera_id, $iniciaEn, $terminaEn, $turno->id)) {
            return response()->json([
                'message' => 'La peluquera ya tiene un turno en ese horario.',
            ], 422);
        }

        $turno->update(['inicia_en' => $iniciaEn, 'termina_en' => $terminaEn]);

        SincronizarTurnoGoogleCalendar::dispatch($turno->id, 'actualizar');

        return response()->json(['message' => 'Turno reagendado correctamente.']);
    }

    /**
     * Cambiar el estado (confirmado / cancelado / pendiente).
     */
    public function cambiarEstado(Request $request, Turno $turno): JsonResponse
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,confirmado,cancelado',
        ]);

        $turno->update(['estado' => $validated['estado']]);

        // Si se cancela, se borra el evento espejo de Google; si no, se actualiza.
        $accion = $validated['estado'] === 'cancelado' ? 'eliminar' : 'actualizar';
        SincronizarTurnoGoogleCalendar::dispatch($turno->id, $accion);

        return response()->json(['message' => 'Estado actualizado.']);
    }

    /**
     * Eliminar un turno.
     */
    public function destroy(Turno $turno): JsonResponse
    {
        $googleEventId = $turno->google_event_id;
        $turno->delete();

        if ($googleEventId) {
            SincronizarTurnoGoogleCalendar::dispatch($turno->id, 'eliminar', $googleEventId);
        }

        return response()->json(['message' => 'Turno eliminado.']);
    }

    /**
     * Actualiza el teléfono del cliente desde el modal de turno, si lo cambiaron
     * y difiere del que ya tiene guardado. Permite corregirlo sin ir a su ficha.
     */
    private function actualizarTelefonoCliente(int $clientId, ?string $telefono): void
    {
        if ($telefono === null) {
            return;
        }

        $telefono = trim($telefono);
        $client = Client::find($clientId);

        if ($client && $client->phone !== $telefono) {
            $client->update(['phone' => $telefono]);
        }
    }

    /**
     * Duración del turno en minutos: la suma de los servicios elegidos, o 30 por defecto.
     */
    private function duracion(array $servicioIds): int
    {
        if (empty($servicioIds)) {
            return 30;
        }

        return Servicio::whereIn('id', $servicioIds)->sum('duracion_minutos') ?: 30;
    }
}
