<?php

namespace App\Http\Controllers;

use App\Jobs\SincronizarTurnoGoogleCalendar;
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
            'client_id' => 'required|exists:clients,id',
            'peluquera_id' => 'nullable|exists:peluqueras,id',
            'servicio_id' => 'nullable|exists:servicios,id',
            'inicia_en' => 'required|date',
            'estado' => 'nullable|in:pendiente,confirmado,cancelado',
            'notas' => 'nullable|string',
        ]);

        $iniciaEn = Carbon::parse($validated['inicia_en']);
        $terminaEn = (clone $iniciaEn)->addMinutes($this->duracion($validated['servicio_id'] ?? null));

        if (! empty($validated['peluquera_id'])
            && Turno::haySolapamiento($validated['peluquera_id'], $iniciaEn, $terminaEn)) {
            return response()->json([
                'message' => 'La peluquera ya tiene un turno en ese horario.',
            ], 422);
        }

        $turno = Turno::create([
            'client_id' => $validated['client_id'],
            'peluquera_id' => $validated['peluquera_id'] ?? null,
            'servicio_id' => $validated['servicio_id'] ?? null,
            'inicia_en' => $iniciaEn,
            'termina_en' => $terminaEn,
            'estado' => $validated['estado'] ?? 'pendiente',
            'notas' => $validated['notas'] ?? null,
        ]);

        SincronizarTurnoGoogleCalendar::dispatch($turno->id, 'crear');

        return response()->json([
            'message' => 'Turno creado correctamente.',
            'turno' => $turno->load(['client', 'peluquera', 'servicio'])->aEventoCalendario(),
        ], 201);
    }

    /**
     * Editar un turno existente.
     */
    public function update(Request $request, Turno $turno): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'peluquera_id' => 'nullable|exists:peluqueras,id',
            'servicio_id' => 'nullable|exists:servicios,id',
            'inicia_en' => 'required|date',
            'estado' => 'required|in:pendiente,confirmado,cancelado',
            'notas' => 'nullable|string',
        ]);

        $iniciaEn = Carbon::parse($validated['inicia_en']);
        $terminaEn = (clone $iniciaEn)->addMinutes($this->duracion($validated['servicio_id'] ?? null));

        if (! empty($validated['peluquera_id'])
            && Turno::haySolapamiento($validated['peluquera_id'], $iniciaEn, $terminaEn, $turno->id)) {
            return response()->json([
                'message' => 'La peluquera ya tiene un turno en ese horario.',
            ], 422);
        }

        $turno->update([
            'client_id' => $validated['client_id'],
            'peluquera_id' => $validated['peluquera_id'] ?? null,
            'servicio_id' => $validated['servicio_id'] ?? null,
            'inicia_en' => $iniciaEn,
            'termina_en' => $terminaEn,
            'estado' => $validated['estado'],
            'notas' => $validated['notas'] ?? null,
        ]);

        SincronizarTurnoGoogleCalendar::dispatch($turno->id, 'actualizar');

        return response()->json([
            'message' => 'Turno actualizado correctamente.',
            'turno' => $turno->load(['client', 'peluquera', 'servicio'])->aEventoCalendario(),
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
        $duracion = $turno->servicio?->duracion_minutos ?? $turno->inicia_en->diffInMinutes($turno->termina_en);
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
     * Duración del turno en minutos: la del servicio si hay, o 30 por defecto.
     */
    private function duracion(?int $servicioId): int
    {
        if ($servicioId) {
            return Servicio::find($servicioId)?->duracion_minutos ?: 30;
        }

        return 30;
    }
}
