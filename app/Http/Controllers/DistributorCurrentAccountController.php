<?php

namespace App\Http\Controllers;

use App\Models\DistributorClient;
use App\Models\DistributorCurrentAccount;
use App\Models\DistributorTechnicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DistributorCurrentAccountController extends Controller
{
    /**
     * Mostrar la lista de cuentas corrientes de todos los distribuidores
     */
    public function index(Request $request)
    {
        // Obtener todos los distribuidores, incluso los que no tienen movimientos
        $query = DistributorClient::orderBy('name')->orderBy('surname');

        // Aplicar filtro de búsqueda si se proporciona
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('surname', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('dni', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                  ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$searchTerm}%"]);
            });
        }

        // Obtener todos los distribuidores para calcular saldos y aplicar filtro
        $allDistributorClients = $query->get();

        // Calcular saldos para todos los distribuidores
        foreach ($allDistributorClients as $client) {
            $client->current_balance = $client->getCurrentBalance();
            $client->formatted_balance = $client->getFormattedBalance();
        }

        // Aplicar filtro por estado de deuda
        if ($request->filled('debt_status')) {
            $debtStatus = $request->debt_status;
            $allDistributorClients = $allDistributorClients->filter(function($client) use ($debtStatus) {
                switch ($debtStatus) {
                    case 'with_debt':
                        return $client->current_balance > 0;
                    case 'up_to_date':
                        return $client->current_balance == 0;
                    case 'in_favor':
                        return $client->current_balance < 0;
                    default:
                        return true;
                }
            });
        }

        // Convertir a paginación manual
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $distributorClients = new \Illuminate\Pagination\LengthAwarePaginator(
            $allDistributorClients->slice($offset, $perPage)->values(),
            $allDistributorClients->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );

        // Agregar parámetros de consulta a la paginación
        $distributorClients->appends($request->query());

        return view('distributor_current_accounts.index', compact('distributorClients'));
    }

    /**
     * Mostrar la cuenta corriente de un distribuidor específico
     */
    public function show(DistributorClient $distributorClient)
    {
        $currentAccounts = $distributorClient->currentAccounts()
            ->with(['user', 'distributorTechnicalRecord'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $currentBalance = $distributorClient->getCurrentBalance();
        $formattedBalance = $distributorClient->getFormattedBalance();

        // Obtener registros técnicos para crear deudas
        $technicalRecords = $distributorClient->distributorTechnicalRecords()
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('distributor_current_accounts')
                      ->whereRaw('distributor_current_accounts.distributor_technical_record_id = distributor_technical_records.id');
            })
            ->get();

        return view('distributor_current_accounts.show', compact(
            'distributorClient',
            'currentAccounts',
            'currentBalance',
            'formattedBalance',
            'technicalRecords'
        ));
    }

    /**
     * Mostrar formulario para crear un nuevo movimiento
     */
    public function create(DistributorClient $distributorClient)
    {
        $technicalRecords = $distributorClient->distributorTechnicalRecords()
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('distributor_current_accounts')
                      ->whereRaw('distributor_current_accounts.distributor_technical_record_id = distributor_technical_records.id');
            })
            ->get();

        return view('distributor_current_accounts.create', compact('distributorClient', 'technicalRecords'));
    }

    /**
     * Guardar un nuevo movimiento
     */
    public function store(Request $request, DistributorClient $distributorClient)
    {
        $request->validate([
            'type' => 'required|in:debt,payment',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'distributor_technical_record_id' => 'nullable|exists:distributor_technical_records,id'
        ]);

        try {
            DB::beginTransaction();

            $currentAccount = DistributorCurrentAccount::create([
                'distributor_client_id' => $distributorClient->id,
                'user_id' => Auth::id(),
                'distributor_technical_record_id' => $request->distributor_technical_record_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
                'reference' => $request->reference,
                'observations' => $request->observations
            ]);

            DB::commit();

            return redirect()->route('distributor-clients.current-accounts.show', $distributorClient)
                ->with('success', 'Movimiento registrado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al registrar el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario para editar un movimiento
     */
    public function edit(DistributorClient $distributorClient, DistributorCurrentAccount $currentAccount)
    {
        $technicalRecords = $distributorClient->distributorTechnicalRecords()
            ->whereNotExists(function($query) use ($currentAccount) {
                $query->select(DB::raw(1))
                      ->from('distributor_current_accounts')
                      ->whereRaw('distributor_current_accounts.distributor_technical_record_id = distributor_technical_records.id')
                      ->where('distributor_current_accounts.id', '!=', $currentAccount->id);
            })
            ->orWhereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('distributor_current_accounts')
                      ->whereRaw('distributor_current_accounts.distributor_technical_record_id = distributor_technical_records.id');
            })
            ->get();

        return view('distributor_current_accounts.edit', compact('distributorClient', 'currentAccount', 'technicalRecords'));
    }

    /**
     * Actualizar un movimiento
     */
    public function update(Request $request, DistributorClient $distributorClient, DistributorCurrentAccount $currentAccount)
    {
        $request->validate([
            'type' => 'required|in:debt,payment',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
            'distributor_technical_record_id' => 'nullable|exists:distributor_technical_records,id'
        ]);

        try {
            DB::beginTransaction();

            $currentAccount->update([
                'distributor_technical_record_id' => $request->distributor_technical_record_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
                'reference' => $request->reference,
                'observations' => $request->observations
            ]);

            DB::commit();

            return redirect()->route('distributor-clients.current-accounts.show', $distributorClient)
                ->with('success', 'Movimiento actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al actualizar el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un movimiento
     */
    public function destroy(DistributorClient $distributorClient, DistributorCurrentAccount $currentAccount)
    {
        try {
            $currentAccount->delete();
            return redirect()->route('distributor-clients.current-accounts.show', $distributorClient)
                ->with('success', 'Movimiento eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar toda la cuenta corriente de un distribuidor
     */
    public function destroyAll(DistributorClient $distributorClient)
    {
        try {
            DB::beginTransaction();

            // Eliminar todos los movimientos de cuenta corriente del distribuidor
            $deletedCount = DistributorCurrentAccount::where('distributor_client_id', $distributorClient->id)->delete();

            DB::commit();

            return redirect()->route('distributor-current-accounts.index')
                ->with('success', "Se eliminaron {$deletedCount} movimientos de la cuenta corriente de {$distributorClient->full_name}.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar la cuenta corriente: ' . $e->getMessage());
        }
    }

    /**
     * Crear deuda automáticamente desde un registro técnico
     */
    public function createFromTechnicalRecord(DistributorClient $distributorClient, DistributorTechnicalRecord $technicalRecord)
    {
        try {
            DB::beginTransaction();

            // Verificar si ya existe un movimiento para este registro técnico
            $existingMovement = DistributorCurrentAccount::where('distributor_technical_record_id', $technicalRecord->id)->first();
            
            if ($existingMovement) {
                return back()->with('error', 'Ya existe un movimiento para este registro técnico.');
            }

            // Calcular el monto de la deuda (total - pago por adelantado)
            $debtAmount = $technicalRecord->final_amount;

            if ($debtAmount > 0) {
                DistributorCurrentAccount::create([
                    'distributor_client_id' => $distributorClient->id,
                    'user_id' => Auth::id(),
                    'distributor_technical_record_id' => $technicalRecord->id,
                    'type' => 'debt',
                    'amount' => $debtAmount,
                    'description' => 'Deuda por compra - ' . $technicalRecord->purchase_date->format('d/m/Y'),
                    'date' => $technicalRecord->purchase_date,
                    'reference' => 'Registro #' . $technicalRecord->id,
                    'observations' => 'Generado automáticamente desde registro técnico'
                ]);
            }

            DB::commit();

            return redirect()->route('distributor-clients.current-accounts.show', $distributorClient)
                ->with('success', 'Deuda creada automáticamente desde el registro técnico.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear la deuda: ' . $e->getMessage());
        }
    }

    /**
     * Exportar cuenta corriente a PDF
     */
    public function exportToPdf(DistributorClient $distributorClient)
    {
        $currentAccounts = $distributorClient->currentAccounts()
            ->with(['user', 'distributorTechnicalRecord'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $currentBalance = $distributorClient->getCurrentBalance();
        $formattedBalance = $distributorClient->getFormattedBalance();

        // Calcular totales
        $totalDebts = $currentAccounts->where('type', 'debt')->sum('amount');
        $totalPayments = $currentAccounts->where('type', 'payment')->sum('amount');

        $data = [
            'distributorClient' => $distributorClient,
            'currentAccounts' => $currentAccounts,
            'currentBalance' => $currentBalance,
            'formattedBalance' => $formattedBalance,
            'totalDebts' => $totalDebts,
            'totalPayments' => $totalPayments,
            'generatedAt' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('distributor_current_accounts.pdf', $data);
        
        $filename = 'cuenta_corriente_' . str_replace(' ', '_', $distributorClient->full_name) . '_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
