<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Turno;
use App\Services\GoogleCalendarService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    /**
     * Vista principal del calendario.
     */
    public function index()
    {
        // Turnos importados desde Google Calendar que todavía no tienen cliente.
        $sinAsignar = Turno::whereNull('client_id')
            ->where('estado', '!=', 'cancelado')
            ->where('termina_en', '>=', Carbon::now())
            ->count();

        $servicios = Servicio::activos()->orderBy('nombre')->get(['id', 'nombre', 'duracion_minutos']);

        // Paleta de colores de Google Calendar (mismos hex que usa Google).
        $nombresColores = [
            '1' => 'Lavanda', '2' => 'Salvia', '3' => 'Uva', '4' => 'Flamenco',
            '5' => 'Banana', '6' => 'Mandarina', '7' => 'Pavo real', '8' => 'Grafito',
            '9' => 'Arándano', '10' => 'Albahaca', '11' => 'Tomate',
        ];
        $coloresGoogle = collect(GoogleCalendarService::COLORES_GOOGLE)
            ->map(fn ($hex, $id) => ['hex' => $hex, 'nombre' => $nombresColores[$id] ?? $hex])
            ->values();

        // Clientes vía AJAX (clients.buscar).
        return view('agenda.index', compact('sinAsignar', 'servicios', 'coloresGoogle'));
    }

    /**
     * Feed JSON de turnos para FullCalendar (filtrado por rango y filtros laterales).
     */
    public function eventos(Request $request): JsonResponse
    {
        $query = Turno::query()
            ->with(['client', 'peluquera', 'servicios'])
            ->when($request->filled('start'), fn ($q) => $q->where('inicia_en', '>=', $request->get('start')))
            ->when($request->filled('end'), fn ($q) => $q->where('inicia_en', '<=', $request->get('end')))
            ->when($request->filled('peluquera_id'), fn ($q) => $q->where('peluquera_id', $request->get('peluquera_id')))
            ->when($request->filled('servicio_id'), fn ($q) => $q->whereHas(
                'servicios',
                fn ($sq) => $sq->where('servicios.id', $request->get('servicio_id'))
            ));

        $eventos = $query->get()->map(fn (Turno $turno) => $turno->aEventoCalendario());

        return response()->json($eventos);
    }

    /**
     * Exportar la agenda de un día a PDF para imprimir.
     */
    public function exportarPdfDia(Request $request)
    {
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->get('fecha'))
            : Carbon::today();

        $turnos = Turno::with(['client', 'peluquera', 'servicios'])
            ->whereDate('inicia_en', $fecha->toDateString())
            ->where('estado', '!=', 'cancelado')
            ->orderBy('inicia_en')
            ->get();

        $pdf = Pdf::loadView('agenda.pdf-dia', compact('turnos', 'fecha'));

        return $pdf->download('agenda-' . $fecha->format('Y-m-d') . '.pdf');
    }
}
