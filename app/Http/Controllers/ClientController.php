<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Client::with(['technicalRecords', 'currentAccounts']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('surname', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('dni', 'LIKE', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(10);

        // Calcular información de cuenta corriente para cada cliente
        foreach ($clients as $client) {
            $client->current_balance = \App\Models\ClientCurrentAccount::getCurrentBalance($client->id);
            $client->has_debt = \App\Models\ClientCurrentAccount::hasDebt($client->id);
        }

        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients',
            'dni' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'observations' => 'nullable|string'
        ]);

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $technicalRecords = $client->technicalRecords()
            ->orderBy('service_date', 'desc')
            ->paginate(5);

        return view('clients.show', compact('client', 'technicalRecords'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email,' . $client->id,
            'dni' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'domicilio' => 'nullable|string',
            'observations' => 'nullable|string'
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Restaurar un cliente desactivado.
     */
    public function restore($id)
    {
        $client = Client::withTrashed()->findOrFail($id);
        $client->restore();
        return redirect()->route('clients.index')
            ->with('success', 'Cliente reactivado exitosamente.');
    }
}
