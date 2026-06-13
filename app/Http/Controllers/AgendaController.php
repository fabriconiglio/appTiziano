<?php

namespace App\Http\Controllers;

use App\Models\Turno;
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
        // Clientes vía AJAX (clients.buscar). Peluquera/servicio quedan fuera del
        // alta por ahora — el turno se crea solo con cliente + fecha.
        return view('agenda.index');
    }

    /**
     * Feed JSON de turnos para FullCalendar (filtrado por rango y filtros laterales).
     */
    public function eventos(Request $request): JsonResponse
    {
        $query = Turno::query()
            ->with(['client', 'peluquera', 'servicio'])
            ->when($request->filled('start'), fn ($q) => $q->where('inicia_en', '>=', $request->get('start')))
            ->when($request->filled('end'), fn ($q) => $q->where('inicia_en', '<=', $request->get('end')))
            ->when($request->filled('peluquera_id'), fn ($q) => $q->where('peluquera_id', $request->get('peluquera_id')))
            ->when($request->filled('servicio_id'), fn ($q) => $q->where('servicio_id', $request->get('servicio_id')));

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

        $turnos = Turno::with(['client', 'peluquera', 'servicio'])
            ->whereDate('inicia_en', $fecha->toDateString())
            ->where('estado', '!=', 'cancelado')
            ->orderBy('inicia_en')
            ->get();

        $pdf = Pdf::loadView('agenda.pdf-dia', compact('turnos', 'fecha'));

        return $pdf->download('agenda-' . $fecha->format('Y-m-d') . '.pdf');
    }
}
