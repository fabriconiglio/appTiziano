<?php

namespace App\Http\Controllers;

use App\Models\ClienteNoFrecuente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteNoFrecuenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ClienteNoFrecuente::with('user')->latest();

        // Filtro por búsqueda
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('peluquero', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('servicios', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('telefono', 'LIKE', "%{$searchTerm}%");
            });
        }


        $clientes = $query->paginate(15);

        return view('cliente-no-frecuentes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cliente-no-frecuentes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'fecha' => 'required|date|before_or_equal:today',
            'monto' => 'required|numeric|min:0',
            'peluquero' => 'required|string|max:255',
            'servicios' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ], [
            'fecha.required' => 'La fecha es requerida',
            'fecha.before_or_equal' => 'La fecha no puede ser futura',
            'monto.required' => 'El valor del servicio es requerido',
            'monto.min' => 'El valor del servicio debe ser mayor a 0',
            'peluquero.required' => 'El nombre del peluquero es requerido',
        ]);

        $validated['user_id'] = Auth::id();

        ClienteNoFrecuente::create($validated);

        return redirect()->route('cliente-no-frecuentes.index')
            ->with('success', 'Cliente no frecuente registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClienteNoFrecuente $clienteNoFrecuente)
    {
        $clienteNoFrecuente->load('user');
        return view('cliente-no-frecuentes.show', compact('clienteNoFrecuente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClienteNoFrecuente $clienteNoFrecuente)
    {
        return view('cliente-no-frecuentes.edit', compact('clienteNoFrecuente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClienteNoFrecuente $clienteNoFrecuente)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'fecha' => 'required|date|before_or_equal:today',
            'monto' => 'required|numeric|min:0',
            'peluquero' => 'required|string|max:255',
            'servicios' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ], [
            'fecha.required' => 'La fecha es requerida',
            'fecha.before_or_equal' => 'La fecha no puede ser futura',
            'monto.required' => 'El valor del servicio es requerido',
            'monto.min' => 'El valor del servicio debe ser mayor a 0',
            'peluquero.required' => 'El nombre del peluquero es requerido',
        ]);

        $clienteNoFrecuente->update($validated);

        return redirect()->route('cliente-no-frecuentes.index')
            ->with('success', 'Cliente no frecuente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClienteNoFrecuente $clienteNoFrecuente)
    {
        $clienteNoFrecuente->delete();

        return redirect()->route('cliente-no-frecuentes.index')
            ->with('success', 'Cliente no frecuente eliminado exitosamente.');
    }
}
