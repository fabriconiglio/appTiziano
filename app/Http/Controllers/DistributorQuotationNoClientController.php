<?php

namespace App\Http\Controllers;

use App\Models\DistributorQuotationNoClient;
use App\Models\SupplierInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class DistributorQuotationNoClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DistributorQuotationNoClient::with('user')
            ->orderBy('created_at', 'desc');

        // Filtro de búsqueda
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('quotation_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('nombre', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('telefono', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('observations', 'LIKE', "%{$searchTerm}%");
            });
        }

        $quotations = $query->paginate(15);

        return view('distributor-quotation-no-clients.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $supplierInventories = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->orderBy('description', 'asc')
            ->orderBy('product_name', 'asc')
            ->get();

        $quotationNumber = DistributorQuotationNoClient::generateQuotationNumber();
        
        return view('distributor-quotation-no-clients.create', compact('supplierInventories', 'quotationNumber'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'quotation_number' => 'required|string|unique:distributor_quotation_no_clients,quotation_number',
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
                    $path = $photo->store('quotation-no-client-photos', 'public');
                    $photos[] = $path;
                }
            }

            // Crear el presupuesto
            $quotation = DistributorQuotationNoClient::create([
                'user_id' => Auth::id(),
                'nombre' => $validated['nombre'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'quotation_number' => $validated['quotation_number'],
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'quotation_type' => $validated['quotation_type'],
                'tax_percentage' => $validated['tax_percentage'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
                'products_quoted' => $validated['products_quoted'],
                'observations' => $validated['observations'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'photos' => $photos,
                'status' => DistributorQuotationNoClient::STATUS_ACTIVE
            ]);

            // Calcular montos automáticamente
            $quotation->calculateAmounts()->save();

            DB::commit();

            return redirect()->route('distributor-quotation-no-clients.index')
                ->with('success', 'Presupuesto creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear presupuesto para cliente no registrado: ' . $e->getMessage());
            
            return back()->withInput()->with('error', 'Error al crear el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DistributorQuotationNoClient $distributorQuotationNoClient)
    {
        $distributorQuotationNoClient->load('user');
        return view('distributor-quotation-no-clients.show', compact('distributorQuotationNoClient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DistributorQuotationNoClient $distributorQuotationNoClient)
    {
        // Solo permitir editar presupuestos activos
        if (!$distributorQuotationNoClient->isActive()) {
            return redirect()->route('distributor-quotation-no-clients.show', $distributorQuotationNoClient)
                ->with('error', 'No se puede editar un presupuesto que no esté activo.');
        }

        $supplierInventories = SupplierInventory::with(['distributorCategory', 'distributorBrand'])
            ->orderBy('description', 'asc')
            ->orderBy('product_name', 'asc')
            ->get();

        return view('distributor-quotation-no-clients.edit', compact('distributorQuotationNoClient', 'supplierInventories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DistributorQuotationNoClient $distributorQuotationNoClient)
    {
        // Solo permitir editar presupuestos activos
        if (!$distributorQuotationNoClient->isActive()) {
            return redirect()->route('distributor-quotation-no-clients.show', $distributorQuotationNoClient)
                ->with('error', 'No se puede editar un presupuesto que no esté activo.');
        }

        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
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
            $photos = $distributorQuotationNoClient->photos ?? [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('quotation-no-client-photos', 'public');
                    $photos[] = $path;
                }
            }

            // Actualizar el presupuesto
            $distributorQuotationNoClient->update([
                'nombre' => $validated['nombre'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'],
                'quotation_type' => $validated['quotation_type'],
                'tax_percentage' => $validated['tax_percentage'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
                'products_quoted' => $validated['products_quoted'],
                'observations' => $validated['observations'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'photos' => $photos
            ]);

            // Calcular montos automáticamente
            $distributorQuotationNoClient->calculateAmounts()->save();

            DB::commit();

            return redirect()->route('distributor-quotation-no-clients.show', $distributorQuotationNoClient)
                ->with('success', 'Presupuesto actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar presupuesto para cliente no registrado: ' . $e->getMessage());
            
            return back()->withInput()->with('error', 'Error al actualizar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DistributorQuotationNoClient $distributorQuotationNoClient)
    {
        try {
            // Eliminar fotos si existen
            if (!empty($distributorQuotationNoClient->photos)) {
                foreach ($distributorQuotationNoClient->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }

            $distributorQuotationNoClient->delete();

            return redirect()->route('distributor-quotation-no-clients.index')
                ->with('success', 'Presupuesto eliminado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar presupuesto para cliente no registrado: ' . $e->getMessage());
            return back()->with('error', 'Error al eliminar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Exportar presupuesto a PDF
     */
    public function exportToPdf(DistributorQuotationNoClient $distributorQuotationNoClient)
    {
        $distributorQuotationNoClient->load('user');
        
        $data = [
            'quotation' => $distributorQuotationNoClient,
            'generatedAt' => now()->format('d/m/Y H:i:s')
        ];

        $pdf = Pdf::loadView('distributor-quotation-no-clients.pdf', $data);
        
        $filename = 'presupuesto_' . $distributorQuotationNoClient->quotation_number . '_' . str_replace(' ', '_', $distributorQuotationNoClient->nombre ?? 'cliente_no_registrado') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Cambiar estado del presupuesto
     */
    public function changeStatus(Request $request, DistributorQuotationNoClient $distributorQuotationNoClient)
    {
        $request->validate([
            'status' => 'required|in:active,expired,cancelled'
        ]);

        try {
            $distributorQuotationNoClient->update(['status' => $request->status]);

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


