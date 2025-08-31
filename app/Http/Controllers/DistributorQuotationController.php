<?php

namespace App\Http\Controllers;

use App\Models\DistributorClient;
use App\Models\DistributorQuotation;
use App\Models\SupplierInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class DistributorQuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DistributorQuotation::with(['distributorClient', 'user'])
            ->orderBy('created_at', 'desc');

        // Filtro de búsqueda simple
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('quotation_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('distributorClient', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'LIKE', "%{$searchTerm}%")
                           ->orWhere('surname', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhere('observations', 'LIKE', "%{$searchTerm}%");
            });
        }

        $quotations = $query->paginate(15);

        return view('distributor_quotations.index', compact('quotations'));
    }

    /**
     * Mostrar vista para seleccionar cliente antes de crear presupuesto
     */
    public function createSelectClient(Request $request)
    {
        $query = DistributorClient::orderBy('name')->orderBy('surname');

        // Filtro de búsqueda
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('surname', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('dni', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        $distributorClients = $query->paginate(15);
        return view('distributor_quotations.create_select_client', compact('distributorClients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DistributorClient $distributorClient)
    {
        $supplierInventories = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->orderBy('description', 'asc')
            ->orderBy('product_name', 'asc')
            ->get();

        $quotationNumber = DistributorQuotation::generateQuotationNumber();
        
        return view('distributor_quotations.create', compact('distributorClient', 'supplierInventories', 'quotationNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DistributorClient $distributorClient)
    {
        $validated = $request->validate([
            'quotation_number' => 'required|string|unique:distributor_quotations,quotation_number',
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after:quotation_date',
            'quotation_type' => 'required|in:al_por_mayor,al_por_menor',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_terms' => 'nullable|string|max:255',
            'products_quoted' => 'required|array|min:1',
            'products_quoted.*.product_id' => 'required|exists:supplier_inventories,id',
            'products_quoted.*.quantity' => 'required|integer|min:1',
            'products_quoted.*.price' => 'required|numeric|min:0.01',
            'observations' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048'
        ]);

        try {
            DB::beginTransaction();

            // Procesar las fotos
            $photos = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('quotation-photos', 'public');
                    $photos[] = $path;
                }
            }

            // Crear el presupuesto
            $quotation = DistributorQuotation::create([
                'distributor_client_id' => $distributorClient->id,
                'user_id' => Auth::id(),
                'quotation_number' => $validated['quotation_number'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'quotation_type' => $validated['quotation_type'],
                'tax_percentage' => $validated['tax_percentage'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'payment_terms' => $validated['payment_terms'],
                'delivery_terms' => $validated['delivery_terms'],
                'products_quoted' => $validated['products_quoted'],
                'observations' => $validated['observations'],
                'terms_conditions' => $validated['terms_conditions'],
                'photos' => $photos,
                'status' => DistributorQuotation::STATUS_ACTIVE
            ]);

            // Calcular montos automáticamente
            $quotation->calculateAmounts()->save();

            DB::commit();

            return redirect()->route('distributor-quotations.index')
                ->with('success', 'Presupuesto creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear presupuesto: ' . $e->getMessage());
            
            return back()->withInput()->with('error', 'Error al crear el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DistributorClient $distributorClient, DistributorQuotation $quotation)
    {
        // Verificar que el presupuesto pertenezca al cliente
        if ($quotation->distributor_client_id !== $distributorClient->id) {
            abort(404);
        }

        return view('distributor_quotations.show', compact('distributorClient', 'quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistributorClient $distributorClient, DistributorQuotation $quotation)
    {
        // Verificar que el presupuesto pertenezca al cliente
        if ($quotation->distributor_client_id !== $distributorClient->id) {
            abort(404);
        }

        // Solo permitir editar presupuestos activos
        if (!$quotation->isActive()) {
            return redirect()->route('distributor-clients.quotations.show', [$distributorClient, $quotation])
                ->with('error', 'No se puede editar un presupuesto que no esté activo.');
        }

        $supplierInventories = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->orderBy('description', 'asc')
            ->orderBy('product_name', 'asc')
            ->get();

        return view('distributor_quotations.edit', compact('distributorClient', 'quotation', 'supplierInventories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistributorClient $distributorClient, DistributorQuotation $quotation)
    {
        // Verificar que el presupuesto pertenezca al cliente
        if ($quotation->distributor_client_id !== $distributorClient->id) {
            abort(404);
        }

        // Solo permitir editar presupuestos activos
        if (!$quotation->isActive()) {
            return redirect()->route('distributor-clients.quotations.show', [$distributorClient, $quotation])
                ->with('error', 'No se puede editar un presupuesto que no esté activo.');
        }

        $validated = $request->validate([
            'quotation_date' => 'required|date',
            'valid_until' => 'required|date|after:quotation_date',
            'quotation_type' => 'required|in:al_por_mayor,al_por_menor',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_terms' => 'nullable|string|max:255',
            'products_quoted' => 'required|array|min:1',
            'products_quoted.*.product_id' => 'required|exists:supplier_inventories,id',
            'products_quoted.*.quantity' => 'required|integer|min:1',
            'products_quoted.*.price' => 'required|numeric|min:0.01',
            'observations' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048'
        ]);

        try {
            DB::beginTransaction();

            // Procesar las fotos
            $photos = $quotation->photos ?? [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('quotation-photos', 'public');
                    $photos[] = $path;
                }
            }

            // Actualizar el presupuesto
            $quotation->update([
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'quotation_type' => $validated['quotation_type'],
                'tax_percentage' => $validated['tax_percentage'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'payment_terms' => $validated['payment_terms'],
                'delivery_terms' => $validated['delivery_terms'],
                'products_quoted' => $validated['products_quoted'],
                'observations' => $validated['observations'],
                'terms_conditions' => $validated['terms_conditions'],
                'photos' => $photos
            ]);

            // Calcular montos automáticamente
            $quotation->calculateAmounts()->save();

            DB::commit();

            return redirect()->route('distributor-clients.quotations.show', [$distributorClient, $quotation])
                ->with('success', 'Presupuesto actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar presupuesto: ' . $e->getMessage());
            
            return back()->withInput()->with('error', 'Error al actualizar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistributorClient $distributorClient, DistributorQuotation $quotation)
    {
        // Verificar que el presupuesto pertenezca al cliente
        if ($quotation->distributor_client_id !== $distributorClient->id) {
            abort(404);
        }

        try {
            // Solo permitir eliminar presupuestos activos o vencidos
            

            // Eliminar fotos si existen
            if (!empty($quotation->photos)) {
                foreach ($quotation->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }

            $quotation->delete();

            return redirect()->route('distributor-clients.quotations.index', $distributorClient)
                ->with('success', 'Presupuesto eliminado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar presupuesto: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Exportar presupuesto a PDF
     */
    public function exportToPdf(DistributorClient $distributorClient, DistributorQuotation $quotation)
    {
        // Verificar que el presupuesto pertenezca al cliente
        if ($quotation->distributor_client_id !== $distributorClient->id) {
            abort(404);
        }

        $data = [
            'distributorClient' => $distributorClient,
            'quotation' => $quotation,
            'generatedAt' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('distributor_quotations.pdf', $data);
        
        $filename = 'presupuesto_' . $quotation->quotation_number . '_' . str_replace(' ', '_', $distributorClient->full_name) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Cambiar estado del presupuesto
     */
    public function changeStatus(Request $request, DistributorClient $distributorClient, DistributorQuotation $quotation)
    {
        $request->validate([
            'status' => 'required|in:active,expired,cancelled'
        ]);

        // Verificar que el presupuesto pertenezca al cliente
        if ($quotation->distributor_client_id !== $distributorClient->id) {
            abort(404);
        }

        try {
            $quotation->update(['status' => $request->status]);

            $statusMessages = [
                'active' => 'Presupuesto marcado como activo.',
                'expired' => 'Presupuesto marcado como vencido.',
                'cancelled' => 'Presupuesto marcado como cancelado.'
            ];

            return back()->with('success', $statusMessages[$request->status]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar estado del presupuesto: ' . $e->getMessage());
            return back()->with('error', 'Error al cambiar el estado del presupuesto.');
        }
    }
}
