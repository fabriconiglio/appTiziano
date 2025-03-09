<?php

namespace App\Http\Controllers;

use App\Models\DistributorClient;
use Illuminate\Http\Request;

class DistributorClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DistributorClient::query();

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

        $distributorClients = $query->latest()->paginate(10);

        return view('distributor_clients.index', compact('distributorClients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('distributor_clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'nullable|email|unique:distributor_clients',
            'dni' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'observations' => 'nullable|string'
        ]);

        DistributorClient::create($validated);

        return redirect()->route('distributor_clients.index')
            ->with('success', 'Cliente distribuidor registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DistributorClient $distributorClient)
    {
        return view('distributor_clients.show', compact('distributorClient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistributorClient $distributorClient)
    {
        return view('distributor_clients.edit', compact('distributorClient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistributorClient $distributorClient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'nullable|email|unique:distributor_clients,email,' . $distributorClient->id,
            'dni' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'observations' => 'nullable|string'
        ]);

        $distributorClient->update($validated);

        return redirect()->route('distributor_clients.index')
            ->with('success', 'Cliente distribuidor actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistributorClient $distributorClient)
    {
        $distributorClient->delete();
        return redirect()->route('distributor_clients.index')
            ->with('success', 'Cliente distribuidor eliminado exitosamente.');
    }
}
