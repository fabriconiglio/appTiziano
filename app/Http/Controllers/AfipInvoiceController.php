<?php

namespace App\Http\Controllers;

use App\Models\AfipInvoice;
use App\Models\AfipInvoiceItem;
use App\Models\DistributorClient;
use App\Models\DistributorTechnicalRecord;
use App\Models\Product;
use App\Models\SupplierInventory;
use App\Services\AfipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AfipInvoiceController extends Controller
{
    protected $afipService;

    public function __construct(AfipService $afipService)
    {
        $this->afipService = $afipService;
    }

    /**
     * Mostrar listado de facturas AFIP
     */
    public function index(Request $request)
    {
        $query = AfipInvoice::with(['distributorClient', 'items.product']);

        // Filtro de búsqueda general
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('distributorClient', function($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%")
                                  ->orWhere('cuit', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('facturacion.index', compact('invoices'));
    }

    /**
     * Mostrar formulario para crear nueva factura
     */
    public function create()
    {
        $clients = DistributorClient::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        
        return view('facturacion.create', compact('clients', 'products'));
    }

    /**
     * Obtener compras de un cliente para facturación
     */
    public function getClientPurchases($clientId)
    {
        try {
            // Verificar que el cliente existe
            $client = DistributorClient::findOrFail($clientId);
            
            $purchases = DistributorTechnicalRecord::where('distributor_client_id', $clientId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($purchase) {
                    return [
                        'id' => $purchase->id,
                        'purchase_date' => $purchase->purchase_date->format('d/m/Y'),
                        'total_amount' => number_format($purchase->total_amount, 2, ',', '.'),
                        'purchase_type' => $purchase->purchase_type
                    ];
                });

            return response()->json($purchases);
        } catch (\Exception $e) {
            Log::error('Error al obtener compras del cliente: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar las compras'], 500);
        }
    }

    /**
     * Obtener productos de una ficha técnica para facturación
     */
    public function getTechnicalRecordProducts($technicalRecordId)
    {
        try {
            $technicalRecord = DistributorTechnicalRecord::findOrFail($technicalRecordId);
            $products = [];

            if (!empty($technicalRecord->products_purchased)) {
                foreach ($technicalRecord->products_purchased as $productData) {
                    $supplierInventory = SupplierInventory::find($productData['product_id']);
                    
                    if ($supplierInventory) {
                        // Usar precio con descuento si está disponible, sino calcular según tipo de compra
                        $unitPrice = 0;
                        
                        if (!empty($productData['price']) && $productData['price'] > 0) {
                            // Usar precio con descuento ya aplicado que se guardó en la ficha técnica
                            $unitPrice = $productData['price'];
                        } else {
                            // Si no hay precio guardado, usar el precio base según tipo de compra
                            if ($technicalRecord->purchase_type == 'al_por_mayor') {
                                $unitPrice = $supplierInventory->precio_mayor;
                            } else {
                                $unitPrice = $supplierInventory->precio_menor;
                            }
                        }
                        
                        // Si no hay precio, usar 0 como fallback
                        if (empty($unitPrice)) {
                            $unitPrice = 0;
                        }
                        
                        $products[] = [
                            'product_id' => $supplierInventory->id,
                            'product_name' => $supplierInventory->product_name,
                            'unit_price' => $unitPrice,
                            'quantity' => $productData['quantity']
                        ];
                    }
                }
            }

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error al obtener productos de la ficha técnica: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar los productos de la compra'], 500);
        }
    }

    /**
     * Crear nueva factura
     */
    public function store(Request $request)
    {
        $request->validate([
            'distributor_client_id' => 'required|exists:distributor_clients,id',
            'technical_record_id' => 'required|exists:distributor_technical_records,id',
            'invoice_type' => 'required|in:A,B,C',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:supplier_inventories,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Crear factura
            $invoice = AfipInvoice::create([
                'distributor_client_id' => $request->distributor_client_id,
                'technical_record_id' => $request->technical_record_id,
                'invoice_type' => $request->invoice_type,
                'point_of_sale' => config('afip.point_of_sale', '1'),
                'invoice_number' => $this->getNextInvoiceNumber($request->invoice_type),
                'invoice_date' => $request->invoice_date,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total' => 0,
                'status' => 'draft',
                'notes' => $request->notes
            ]);

            // Crear items
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($request->items as $itemData) {
                $supplierInventory = SupplierInventory::find($itemData['product_id']);
                
                $item = AfipInvoiceItem::create([
                    'afip_invoice_id' => $invoice->id,
                    'product_id' => $supplierInventory->id,
                    'description' => $supplierInventory->product_name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => config('afip.tax_rate', '21.00')
                ]);

                $subtotal += $item->subtotal;
                $taxAmount += $item->tax_amount;
            }

            // Actualizar totales
            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount
            ]);

            DB::commit();

            return redirect()->route('facturacion.show', $invoice->id)
                ->with('success', 'Factura creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando factura: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Error al crear la factura: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar factura específica
     */
    public function show(AfipInvoice $facturacion)
    {
        $facturacion->load(['distributorClient', 'items.product']);
        
        return view('facturacion.show', compact('facturacion'));
    }

    /**
     * Enviar factura a AFIP
     */
    public function sendToAfip(AfipInvoice $facturacion)
    {
        if ($facturacion->status !== 'draft') {
            return back()->with('error', 'Solo se pueden enviar facturas en estado borrador');
        }

        try {
            $result = $this->afipService->createInvoice($facturacion);

            if ($result['success']) {
                return back()->with('success', 
                    'Factura autorizada por AFIP. CAE: ' . $result['cae']);
            } else {
                return back()->with('error', 
                    'Error al autorizar factura: ' . $result['error']);
            }

        } catch (\Exception $e) {
            Log::error('Error enviando factura a AFIP: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar factura: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar factura
     */
    public function cancel(AfipInvoice $facturacion)
    {
        if (!$facturacion->canBeCancelled()) {
            return back()->with('error', 'No se puede cancelar esta factura');
        }

        $facturacion->update(['status' => 'cancelled']);

        return back()->with('success', 'Factura cancelada exitosamente');
    }

    /**
     * Obtener siguiente número de factura
     */
    private function getNextInvoiceNumber(string $invoiceType): int
    {
        $voucherType = $this->getVoucherType($invoiceType);
        $pointOfSale = config('afip.point_of_sale', '1');
        
        $lastNumber = $this->afipService->getLastAuthorizedVoucher($pointOfSale, $voucherType);
        
        return $lastNumber + 1;
    }

    /**
     * Obtener tipo de comprobante AFIP
     */
    private function getVoucherType(string $invoiceType): int
    {
        return match($invoiceType) {
            'A' => 1,
            'B' => 6,
            'C' => 11,
            default => 6
        };
    }

    /**
     * Obtener información del cliente para facturación
     */
    public function getClientInfo(DistributorClient $client)
    {
        return response()->json([
            'name' => $client->name,
            'surname' => $client->surname,
            'full_name' => $client->full_name,
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
            'domicilio' => $client->domicilio ?? ''
        ]);
    }

    /**
     * Obtener información del producto para facturación
     */
    public function getProductInfo(Product $product)
    {
        return response()->json([
            'name' => $product->name,
            'price' => $product->price,
            'description' => $product->description
        ]);
    }
}
