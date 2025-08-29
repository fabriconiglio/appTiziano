<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientCurrentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientCurrentAccountController extends Controller
{
    /**
     * Mostrar lista de todas las cuentas corrientes
     */
    public function index(Request $request)
    {
        $query = Client::with('currentAccounts');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate(15);

        // Calcular saldos
        foreach ($clients as $client) {
            $client->current_balance = ClientCurrentAccount::getCurrentBalance($client->id);
            $client->formatted_balance = ClientCurrentAccount::getFormattedBalance($client->id);
        }

        return view('client_current_accounts.index', compact('clients'));
    }

    /**
     * Mostrar detalles de la cuenta corriente de un cliente
     */
    public function show(Client $client)
    {
        $currentAccounts = $client->currentAccounts()
            ->with('user')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $currentBalance = ClientCurrentAccount::getCurrentBalance($client->id);
        $formattedBalance = ClientCurrentAccount::getFormattedBalance($client->id);

        return view('client_current_accounts.show', compact('client', 'currentAccounts', 'currentBalance', 'formattedBalance'));
    }

    /**
     * Mostrar formulario para crear nuevo movimiento
     */
    public function create(Client $client)
    {
        return view('client_current_accounts.create', compact('client'));
    }

    /**
     * Guardar nuevo movimiento
     */
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'type' => 'required|in:debt,payment',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'observations' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            ClientCurrentAccount::create([
                'client_id' => $client->id,
                'user_id' => Auth::id(),
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'date' => $validated['date'],
                'reference' => $validated['reference'],
                'observations' => $validated['observations']
            ]);

            DB::commit();

            return redirect()->route('clients.current-accounts.show', $client)
                ->with('success', 'Movimiento creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al crear el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario para editar movimiento
     */
    public function edit(Client $client, ClientCurrentAccount $currentAccount)
    {
        return view('client_current_accounts.edit', compact('client', 'currentAccount'));
    }

    /**
     * Actualizar movimiento
     */
    public function update(Request $request, Client $client, ClientCurrentAccount $currentAccount)
    {
        $validated = $request->validate([
            'type' => 'required|in:debt,payment',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'observations' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $currentAccount->update([
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'date' => $validated['date'],
                'reference' => $validated['reference'],
                'observations' => $validated['observations']
            ]);

            DB::commit();

            return redirect()->route('clients.current-accounts.show', $client)
                ->with('success', 'Movimiento actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un movimiento
     */
    public function destroy(Client $client, ClientCurrentAccount $currentAccount)
    {
        try {
            $currentAccount->delete();
            return redirect()->route('clients.current-accounts.show', $client)
                ->with('success', 'Movimiento eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar toda la cuenta corriente de un cliente
     */
    public function destroyAll(Client $client)
    {
        try {
            DB::beginTransaction();

            $deletedCount = ClientCurrentAccount::where('client_id', $client->id)->delete();

            DB::commit();

            return redirect()->route('client-current-accounts.index')
                ->with('success', "Se eliminaron {$deletedCount} movimientos de la cuenta corriente de {$client->full_name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la cuenta corriente: ' . $e->getMessage());
        }
    }
}
