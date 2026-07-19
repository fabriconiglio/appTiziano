<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::orderBy('nombre')->paginate(15);

        return view('servicios.index', compact('servicios'));
    }

    public function create()
    {
        return view('servicios.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        Servicio::create($validated);

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio creado exitosamente.');
    }

    public function edit(Servicio $servicio)
    {
        return view('servicios.edit', compact('servicio'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        $validated = $this->validar($request);
        $servicio->update($validated);

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio eliminado exitosamente.');
    }

    /**
     * Alta rápida de servicio desde el modal de Agenda (JSON). Si ya existe uno
     * con ese nombre (sin importar mayúsculas), lo reutiliza en vez de duplicar.
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'duracion_minutos' => 'nullable|integer|min:5|max:600',
        ]);

        $nombre = trim($validated['nombre']);

        $servicio = Servicio::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first()
            ?? Servicio::create([
                'nombre' => $nombre,
                'duracion_minutos' => $validated['duracion_minutos'] ?? 30,
                'precio_base' => 0,
                'color_default' => '#3788d8',
                'activo' => true,
            ]);

        return response()->json([
            'id' => $servicio->id,
            'nombre' => $servicio->nombre,
            'duracion_minutos' => $servicio->duracion_minutos,
        ], 201);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'duracion_minutos' => 'required|integer|min:5|max:600',
            'precio_base' => 'nullable|numeric|min:0',
            'color_default' => 'nullable|string|max:20',
            'activo' => 'nullable|boolean',
        ]);
    }
}
