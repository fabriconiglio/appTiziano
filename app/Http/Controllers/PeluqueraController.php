<?php

namespace App\Http\Controllers;

use App\Models\Peluquera;
use Illuminate\Http\Request;

class PeluqueraController extends Controller
{
    public function index()
    {
        $peluqueras = Peluquera::orderBy('nombre')->paginate(15);

        return view('peluqueras.index', compact('peluqueras'));
    }

    public function create()
    {
        return view('peluqueras.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validar($request);
        Peluquera::create($validated);

        return redirect()->route('peluqueras.index')
            ->with('success', 'Peluquera creada exitosamente.');
    }

    public function edit(Peluquera $peluquera)
    {
        return view('peluqueras.edit', compact('peluquera'));
    }

    public function update(Request $request, Peluquera $peluquera)
    {
        $validated = $this->validar($request);
        $peluquera->update($validated);

        return redirect()->route('peluqueras.index')
            ->with('success', 'Peluquera actualizada exitosamente.');
    }

    public function destroy(Peluquera $peluquera)
    {
        $peluquera->delete();

        return redirect()->route('peluqueras.index')
            ->with('success', 'Peluquera eliminada exitosamente.');
    }

    private function validar(Request $request): array
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'activo' => 'nullable|boolean',
            'horarios' => 'nullable|array',
        ]);

        // Descartar días sin horario (cerrado) y conservar solo los completos.
        $horarios = [];
        foreach ($request->input('horarios', []) as $dia => $rango) {
            $apertura = $rango[0] ?? null;
            $cierre = $rango[1] ?? null;
            if ($apertura && $cierre) {
                $horarios[$dia] = [$apertura, $cierre];
            }
        }
        $validated['horarios'] = $horarios ?: null;

        return $validated;
    }
}
