<?php

namespace App\Http\Controllers;

use App\Models\AfipConfiguration;
use App\Models\AfipInvoice;
use App\Models\AfipInvoiceItem;
use App\Models\Client;
use App\Models\ClienteNoFrecuente;
use App\Models\DistributorClient;
use App\Models\DistributorClienteNoFrecuente;
use App\Models\DistributorTechnicalRecord;
use App\Models\Product;
use App\Models\SupplierInventory;
use App\Models\TechnicalRecord;
use App\Services\AfipService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Generator;

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
        $products = Product::orderBy('name')->get();
        
        return view('facturacion.create', compact('products'));
    }

    /**
     * Obtener compras de un cliente para facturación
     */
    public function getClientPurchases(Request $request, $clientId)
    {
        try {
            $clientType = $request->get('client_type', 'distributor_client');
            $purchases = [];

            switch ($clientType) {
                case 'distributor_client':
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
                    break;

                case 'client':
                    $client = Client::findOrFail($clientId);
                    $purchases = TechnicalRecord::where('client_id', $clientId)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($purchase) {
                            return [
                                'id' => $purchase->id,
                                'purchase_date' => $purchase->service_date->format('d/m/Y'),
                                'total_amount' => number_format($purchase->service_cost ?? 0, 2, ',', '.'),
                                'purchase_type' => 'servicio'
                            ];
                        });
                    break;

                case 'distributor_no_frecuente':
                    // Los clientes no frecuentes tienen su compra directamente en el registro
                    $clienteNoFrecuente = DistributorClienteNoFrecuente::findOrFail($clientId);
                    $purchases = [[
                        'id' => $clienteNoFrecuente->id,
                        'purchase_date' => $clienteNoFrecuente->fecha->format('d/m/Y'),
                        'total_amount' => number_format($clienteNoFrecuente->monto, 2, ',', '.'),
                        'purchase_type' => $clienteNoFrecuente->purchase_type ?? 'al_por_menor'
                    ]];
                    break;

                case 'client_no_frecuente':
                    // Clientes no frecuentes de peluquería tienen su servicio directamente en el registro
                    $clienteNoFrecuente = ClienteNoFrecuente::findOrFail($clientId);
                    $purchases = [[
                        'id' => $clienteNoFrecuente->id,
                        'purchase_date' => $clienteNoFrecuente->fecha->format('d/m/Y'),
                        'total_amount' => number_format($clienteNoFrecuente->monto, 2, ',', '.'),
                        'purchase_type' => 'servicio'
                    ]];
                    break;

                default:
                    throw new \Exception('Tipo de cliente no válido');
            }

            return response()->json($purchases);
        } catch (\Exception $e) {
            Log::error('Error al obtener compras del cliente: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar las compras: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener productos de una ficha técnica para facturación
     * También maneja clientes no frecuentes donde el ID es del cliente mismo
     */
    public function getTechnicalRecordProducts(Request $request, $technicalRecordId)
    {
        try {
            $clientType = $request->get('client_type', 'distributor_client');
            $products = [];

            if ($clientType === 'distributor_no_frecuente') {
                // Cliente no frecuente de distribuidora - obtener productos directamente del registro
                $clienteNoFrecuente = DistributorClienteNoFrecuente::findOrFail($technicalRecordId);
                
                if (!empty($clienteNoFrecuente->products_purchased) && is_array($clienteNoFrecuente->products_purchased)) {
                    foreach ($clienteNoFrecuente->products_purchased as $productData) {
                        $supplierInventory = SupplierInventory::find($productData['product_id']);
                        
                        if ($supplierInventory) {
                            // Usar precio guardado en la compra
                            $unitPrice = $productData['price'] ?? 0;
                            
                            $products[] = [
                                'product_id' => $supplierInventory->id,
                                'product_name' => $supplierInventory->product_name,
                                'unit_price' => $unitPrice,
                                'quantity' => $productData['quantity'] ?? 1
                            ];
                        }
                    }
                }
            } elseif ($clientType === 'client_no_frecuente') {
                // Cliente no frecuente de peluquería - es un servicio
                $clienteNoFrecuente = ClienteNoFrecuente::findOrFail($technicalRecordId);
                
                $serviceDescription = 'Servicio de peluquería';
                if ($clienteNoFrecuente->servicios) {
                    $serviceDescription = $clienteNoFrecuente->servicios;
                }
                
                $products[] = [
                    'product_id' => null,
                    'product_name' => $serviceDescription,
                    'unit_price' => $clienteNoFrecuente->monto ?? 0,
                    'quantity' => 1
                ];
            } elseif ($clientType === 'client') {
                // Cliente de peluquería - usar TechnicalRecord
                // En peluquería es un servicio, no productos individuales
                $technicalRecord = TechnicalRecord::findOrFail($technicalRecordId);
                
                // Crear un único item con la descripción del servicio
                $serviceDescription = $technicalRecord->service_description ?? 'Servicio de peluquería';
                
                // Si hay tipo de servicio, agregarlo a la descripción
                if ($technicalRecord->service_type) {
                    $serviceDescription = $technicalRecord->service_type . ($serviceDescription ? ' - ' . $serviceDescription : '');
                }
                
                // Si hay tratamientos, agregarlos
                if ($technicalRecord->hair_treatments) {
                    $serviceDescription .= ($serviceDescription ? ' - ' : '') . $technicalRecord->hair_treatments;
                }
                
                $products[] = [
                    'product_id' => null, // No hay producto específico, es un servicio
                    'product_name' => $serviceDescription ?: 'Servicio de peluquería',
                    'unit_price' => $technicalRecord->service_cost ?? 0,
                    'quantity' => 1
                ];
            } else {
                // Cliente de distribuidora - usar DistributorTechnicalRecord
                $technicalRecord = DistributorTechnicalRecord::findOrFail($technicalRecordId);
                
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
            }

            return response()->json($products);
        } catch (\Exception $e) {
            Log::error('Error al obtener productos de la ficha técnica: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar los productos de la compra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Crear nueva factura
     */
    public function store(Request $request)
    {
        // Validación base
        $rules = [
            'client_type' => 'required|in:distributor_client,client,distributor_no_frecuente,client_no_frecuente',
            'client_id' => 'required|integer',
            'invoice_type' => 'required|in:A,B,C',
            'invoice_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ];

        // Validar product_id según el tipo de cliente
        if ($request->client_type === 'client') {
            // Cliente de peluquería - productos pueden ser de la tabla products o null (servicio sin producto)
            $rules['items.*.product_id'] = 'nullable|exists:products,id';
        } else {
            // Cliente de distribuidora - productos deben ser de supplier_inventories
            $rules['items.*.product_id'] = 'required|exists:supplier_inventories,id';
        }

        // Validar technical_record_id según el tipo de cliente
        if ($request->client_type === 'distributor_client') {
            $rules['technical_record_id'] = 'required|exists:distributor_technical_records,id';
        } elseif ($request->client_type === 'client') {
            $rules['technical_record_id'] = 'required|exists:technical_records,id';
        } elseif ($request->client_type === 'distributor_no_frecuente') {
            // Para clientes no frecuentes, el technical_record_id es el ID del cliente mismo
            $rules['technical_record_id'] = 'required|exists:distributor_cliente_no_frecuentes,id';
        } elseif ($request->client_type === 'client_no_frecuente') {
            // Para clientes no frecuentes de peluquería, el technical_record_id es el ID del cliente mismo
            $rules['technical_record_id'] = 'required|exists:cliente_no_frecuentes,id';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Validar que el cliente existe según su tipo
            $clientExists = false;
            $distributorClientId = null;

            switch ($request->client_type) {
                case 'distributor_client':
                    $client = DistributorClient::find($request->client_id);
                    $clientExists = $client !== null;
                    $distributorClientId = $request->client_id;
                    break;
                case 'client':
                    $client = Client::find($request->client_id);
                    $clientExists = $client !== null;
                    break;
                case 'distributor_no_frecuente':
                    $client = DistributorClienteNoFrecuente::find($request->client_id);
                    $clientExists = $client !== null;
                    break;
                case 'client_no_frecuente':
                    $client = ClienteNoFrecuente::find($request->client_id);
                    $clientExists = $client !== null;
                    break;
            }

            if (!$clientExists) {
                return back()->withInput()
                    ->with('error', 'El cliente seleccionado no existe');
            }

            // Crear factura
            $invoice = AfipInvoice::create([
                'distributor_client_id' => $distributorClientId, // Solo para compatibilidad con distribuidora
                'client_type' => $request->client_type,
                'client_id' => $request->client_id,
                'technical_record_id' => $request->technical_record_id ?? null,
                'invoice_type' => $request->invoice_type,
                'point_of_sale' => AfipConfiguration::get('afip_point_of_sale', '5'),
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
                $productId = $itemData['product_id'] ?? null;
                $description = $itemData['product_name'] ?? 'Producto sin especificar';
                
                // Manejar productos según el tipo de cliente
                if ($request->client_type === 'client') {
                    // Cliente de peluquería - productos de la tabla products
                    if ($productId) {
                        $product = Product::find($productId);
                        if ($product) {
                            $description = $product->name;
                            // Para facturación AFIP, necesitamos un ID de producto válido
                            // Como los productos de peluquería no están en supplier_inventories,
                            // podemos usar null o crear un registro temporal
                            $productId = null; // No hay correspondencia directa con supplier_inventories
                        }
                    }
                    // Si no hay product_id, es un servicio sin producto específico
                    // La descripción ya viene en product_name del itemData
                } else {
                    // Cliente de distribuidora - productos de supplier_inventories
                    $supplierInventory = SupplierInventory::find($productId);
                    if ($supplierInventory) {
                        $description = $supplierInventory->product_name;
                        $productId = $supplierInventory->id;
                    } else {
                        // Si no se encuentra, continuar con la descripción proporcionada
                        $productId = null;
                    }
                }
                
                $item = AfipInvoiceItem::create([
                    'afip_invoice_id' => $invoice->id,
                    'product_id' => $productId, // Puede ser null para productos de peluquería
                    'description' => $description,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => config('afip.tax_rate', '21.00')
                ]);

                $subtotal += $item->subtotal;
                $taxAmount += $item->tax_amount;
            }

            // Actualizar totales
            // El total es igual al subtotal porque el IVA ya está incluido en los precios
            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount, // IVA calculado internamente para AFIP
                'total' => $subtotal // Total igual al subtotal (IVA incluido)
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
        // Permitir reenvío de facturas rechazadas
        if (!in_array($facturacion->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Solo se pueden enviar facturas en estado borrador o rechazadas');
        }

        try {
            // Si la factura está rechazada, resetear el estado antes de reenviar
            if ($facturacion->status === 'rejected') {
                $facturacion->update([
                    'status' => 'draft',
                    'cae' => null,
                    'cae_expiration' => null,
                    'afip_response' => null
                ]);
            }

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
     * Descargar PDF de la factura con formato oficial AFIP
     */
    public function downloadPdf(AfipInvoice $facturacion)
    {
        if ($facturacion->status !== 'authorized') {
            return back()->with('error', 'Solo se pueden descargar facturas autorizadas');
        }

        try {
            // Cargar relaciones necesarias
            $facturacion->load(['distributorClient', 'items.product']);
            
            // Obtener configuración AFIP
            $config = AfipConfiguration::getAfipConfig();
            
            // Generar código de barras del CAE
            $barcodeData = $this->generateBarcodeData($facturacion, $config);
            
            // Generar QR code para verificación AFIP
            try {
                $qrCode = $this->generateQrCode($facturacion, $config);
            } catch (\Exception $e) {
                Log::warning('Error generando QR code, usando código de barras numérico: ' . $e->getMessage());
                $qrCode = null; // Fallback a código de barras numérico
            }
            
            // Generar PDF
            $pdf = PDF::loadView('facturacion.pdf', [
                'invoice' => $facturacion,
                'config' => $config,
                'barcodeData' => $barcodeData,
                'qrCode' => $qrCode,
                'generatedAt' => now()->format('d/m/Y H:i')
            ]);
            
            // Configurar opciones del PDF
            $pdf->setPaper('a4', 'portrait');
            
            return $pdf->download('Factura-' . $facturacion->formatted_number . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Error generando PDF de factura: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generar QR code para verificación AFIP
     */
    private function generateQrCode(AfipInvoice $invoice, array $config): string
    {
        $client = $invoice->getClient();
        $dni = 0;
        $tipoDocRec = 99; // Sin documento por defecto
        
        if ($client) {
            if ($invoice->client_type === 'distributor_client' || $invoice->client_type === 'client' || !$invoice->client_type) {
                $dni = $client->dni ?? 0;
            } else {
                // Clientes no frecuentes pueden no tener DNI
                $dni = 0;
            }
            
            if ($dni) {
                $tipoDocRec = 96; // DNI
            }
        }
        
        // URL de verificación AFIP con los datos del comprobante
        $verificationUrl = "https://www.afip.gob.ar/fe/qr/?p=" . urlencode(json_encode([
            'ver' => 1,
            'fecha' => $invoice->invoice_date->format('Y-m-d'),
            'cuit' => $config['cuit'],
            'ptoVta' => $invoice->point_of_sale,
            'tipoCmp' => $this->getVoucherType($invoice->invoice_type),
            'nroCmp' => $invoice->invoice_number,
            'importe' => $invoice->total,
            'moneda' => 'PES',
            'ctz' => 1,
            'tipoDocRec' => $tipoDocRec,
            'nroDocRec' => $dni,
            'tipoCodAut' => 'E',
            'codAut' => $invoice->cae
        ]));
        
        // Generar QR code como data URI (base64)
        $qrGenerator = new Generator();
        return 'data:image/png;base64,' . base64_encode(
            $qrGenerator->format('png')
                ->size(150)
                ->margin(1)
                ->generate($verificationUrl)
        );
    }

    /**
     * Generar datos del código de barras del CAE según especificaciones AFIP
     */
    private function generateBarcodeData(AfipInvoice $invoice, array $config): string
    {
        // Formato del código de barras según AFIP:
        // CUIT (11) + Tipo Comprobante (3) + Punto Venta (5) + CAE (14) + Vencimiento CAE (8)
        
        $cuit = str_pad($config['cuit'], 11, '0', STR_PAD_LEFT);
        $voucherType = str_pad($this->getVoucherType($invoice->invoice_type), 3, '0', STR_PAD_LEFT);
        $pointOfSale = str_pad($invoice->point_of_sale, 5, '0', STR_PAD_LEFT);
        $cae = str_pad($invoice->cae, 14, '0', STR_PAD_LEFT);
        $caeExpiration = $invoice->cae_expiration->format('Ymd');
        
        return $cuit . $voucherType . $pointOfSale . $cae . $caeExpiration;
    }

    /**
     * Obtener siguiente número de factura
     */
    private function getNextInvoiceNumber(string $invoiceType): int
    {
        $voucherType = $this->getVoucherType($invoiceType);
        $pointOfSale = AfipConfiguration::get('afip_point_of_sale', '5');
        
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

    /**
     * Búsqueda unificada de clientes (todos los tipos)
     */
    public function searchClients(Request $request)
    {
        $search = $request->get('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Buscar en Clientes de Distribuidora
        $distributorClients = DistributorClient::where(function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('surname', 'LIKE', "%{$search}%")
                  ->orWhere('dni', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
        })
        ->limit(50)
        ->get();

        foreach ($distributorClients as $client) {
            $dni = $client->dni ? " - {$client->dni}" : '';
            $results[] = [
                'id' => "distributor_client_{$client->id}",
                'text' => "{$client->full_name}{$dni} - Cliente Distribuidora",
                'client_type' => 'distributor_client',
                'client_id' => $client->id,
                'name' => $client->name,
                'surname' => $client->surname,
                'dni' => $client->dni,
                'email' => $client->email,
                'phone' => $client->phone,
                'domicilio' => $client->domicilio ?? ''
            ];
        }

        // Buscar en Clientes de Peluquería
        $clients = Client::where(function($query) use ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('surname', 'LIKE', "%{$search}%")
                  ->orWhere('dni', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
        })
        ->limit(50)
        ->get();

        foreach ($clients as $client) {
            $dni = $client->dni ? " - {$client->dni}" : '';
            $results[] = [
                'id' => "client_{$client->id}",
                'text' => "{$client->full_name}{$dni} - Cliente Peluquería",
                'client_type' => 'client',
                'client_id' => $client->id,
                'name' => $client->name,
                'surname' => $client->surname,
                'dni' => $client->dni,
                'email' => $client->email,
                'phone' => $client->phone,
                'domicilio' => $client->domicilio ?? ''
            ];
        }

        // Buscar en Clientes No Frecuentes de Distribuidora
        $distributorNoFrecuentes = DistributorClienteNoFrecuente::where('nombre', 'LIKE', "%{$search}%")
            ->orWhere('telefono', 'LIKE', "%{$search}%")
            ->limit(50)
            ->get();

        foreach ($distributorNoFrecuentes as $client) {
            $results[] = [
                'id' => "distributor_no_frecuente_{$client->id}",
                'text' => "{$client->nombre} - No Frecuente Distribuidora",
                'client_type' => 'distributor_no_frecuente',
                'client_id' => $client->id,
                'name' => $client->nombre,
                'surname' => '',
                'dni' => '',
                'email' => '',
                'phone' => $client->telefono ?? '',
                'domicilio' => ''
            ];
        }

        // Buscar en Clientes No Frecuentes de Peluquería
        $clientesNoFrecuentes = ClienteNoFrecuente::where('nombre', 'LIKE', "%{$search}%")
            ->orWhere('telefono', 'LIKE', "%{$search}%")
            ->limit(50)
            ->get();

        foreach ($clientesNoFrecuentes as $client) {
            $results[] = [
                'id' => "client_no_frecuente_{$client->id}",
                'text' => "{$client->nombre} - No Frecuente Peluquería",
                'client_type' => 'client_no_frecuente',
                'client_id' => $client->id,
                'name' => $client->nombre,
                'surname' => '',
                'dni' => '',
                'email' => '',
                'phone' => $client->telefono ?? '',
                'domicilio' => ''
            ];
        }

        return response()->json($results);
    }
}
