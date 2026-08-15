<?php

namespace App\Http\Controllers;

use App\Models\DistributorClienteNoFrecuente;
use App\Models\DistributorClient;
use App\Models\DistributorCurrentAccount;
use App\Models\DistributorReturn;
use App\Models\DistributorTechnicalRecord;
use App\Models\SupplierInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Devoluciones de la distribuidora (nota de crédito interna, sin AFIP).
 *
 * Toda devolución sale de una compra concreta y se admite una sola por compra.
 * Los precios salen de la compra original: nunca de la lista de hoy, porque con
 * la inflación se le acreditaría de más al cliente.
 */
class DistributorReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = DistributorReturn::with(['distributorClient', 'clienteNoFrecuente', 'user'])
            ->orderByDesc('return_date')
            ->orderByDesc('id');

        if ($buscar = $request->get('buscar')) {
            $query->where(function ($q) use ($buscar) {
                $q->where('return_number', 'LIKE', "%{$buscar}%")
                    ->orWhereHas('distributorClient', fn ($c) => $c->where('name', 'LIKE', "%{$buscar}%")
                        ->orWhere('surname', 'LIKE', "%{$buscar}%"))
                    ->orWhereHas('clienteNoFrecuente', fn ($c) => $c->where('nombre', 'LIKE', "%{$buscar}%"));
            });
        }

        if ($desde = $request->get('desde')) {
            $query->whereDate('return_date', '>=', $desde);
        }
        if ($hasta = $request->get('hasta')) {
            $query->whereDate('return_date', '<=', $hasta);
        }

        return view('distributor_returns.index', [
            'returns' => $query->paginate(20)->withQueryString(),
            'buscar' => $buscar,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
    }

    public function create()
    {
        return view('distributor_returns.create', [
            'clientes' => DistributorClient::orderBy('name')->get(['id', 'name', 'surname']),
            'diasPlazo' => DistributorReturn::DIAS_PLAZO,
        ]);
    }

    /**
     * Compras de un cliente para elegir cuál devolver (AJAX).
     * Se muestran las de los últimos 90 días: las que pasaron el plazo salen
     * marcadas para que la peluquería decida si autoriza igual.
     */
    public function comprasDeCliente(DistributorClient $distributorClient)
    {
        $compras = DistributorTechnicalRecord::where('distributor_client_id', $distributorClient->id)
            ->where('purchase_date', '>=', now()->subDays(90))
            ->orderByDesc('purchase_date')
            ->get();

        return response()->json($compras->map(function ($compra) {
            $dias = (int) $compra->purchase_date->diffInDays(now());

            return [
                'id' => $compra->id,
                'fecha' => $compra->purchase_date->format('d/m/Y'),
                'total' => (float) ($compra->final_amount ?? $compra->total_amount ?? 0),
                'cantidad_productos' => count($this->productosDeCompra($compra)),
                'dias' => $dias,
                'fuera_de_plazo' => $dias > DistributorReturn::DIAS_PLAZO,
                'ya_devuelta' => DistributorReturn::compraYaDevuelta($compra->id),
                'devolucion' => DistributorReturn::where('distributor_technical_record_id', $compra->id)
                    ->whereNull('anulada_en')->value('return_number'),
            ];
        })->values());
    }

    /**
     * Productos de una compra, con el precio que el cliente pagó (AJAX).
     */
    public function productosDeCompraJson(DistributorTechnicalRecord $distributorTechnicalRecord)
    {
        $items = $this->productosDeCompra($distributorTechnicalRecord);

        if (empty($items)) {
            return response()->json([
                'error' => 'Esta compra no tiene productos cargados, así que no se puede armar la devolución.',
            ], 422);
        }

        $ids = collect($items)->pluck('product_id')->filter()->all();
        $nombres = SupplierInventory::whereIn('id', $ids)->pluck('product_name', 'id');

        return response()->json(collect($items)->map(fn ($item) => [
            'product_id' => (int) $item['product_id'],
            'nombre' => $nombres[(int) $item['product_id']] ?? 'Producto #' . $item['product_id'],
            'cantidad_comprada' => (int) $item['quantity'],
            // 'price' es lo que efectivamente pagó (ya con su descuento aplicado).
            'precio_unitario' => (float) $item['price'],
        ])->values());
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'distributor_technical_record_id' => 'required|exists:distributor_technical_records,id',
            'return_date' => 'required|date',
            'destino' => 'required|in:cuenta_corriente,efectivo,vale',
            'motivo' => 'nullable|string|max:500',
            'observations' => 'nullable|string|max:1000',
            'autorizar_fuera_plazo' => 'nullable|boolean',
            'productos' => 'required|array|min:1',
            'productos.*.product_id' => 'required|integer',
            'productos.*.cantidad' => 'required|integer|min:1',
        ]);

        $compra = DistributorTechnicalRecord::findOrFail($datos['distributor_technical_record_id']);

        if (DistributorReturn::compraYaDevuelta($compra->id)) {
            return back()->withInput()->with('error', 'Esa compra ya tiene una devolución. Sólo se admite una por compra.');
        }

        $itemsCompra = collect($this->productosDeCompra($compra));
        if ($itemsCompra->isEmpty()) {
            return back()->withInput()->with('error', 'Esa compra no tiene productos cargados.');
        }

        // Armar el detalle validando contra lo que realmente compró.
        $productos = [];
        $total = 0;

        foreach ($datos['productos'] as $pedido) {
            $original = $itemsCompra->firstWhere('product_id', (string) $pedido['product_id'])
                ?? $itemsCompra->firstWhere('product_id', $pedido['product_id']);

            if (! $original) {
                return back()->withInput()->with('error', 'Hay un producto que no pertenece a esa compra.');
            }

            if ($pedido['cantidad'] > (int) $original['quantity']) {
                return back()->withInput()->with('error',
                    'No se puede devolver más de lo comprado: se compraron ' . (int) $original['quantity'] . ' unidades.');
            }

            $precio = (float) $original['price'];
            $subtotal = $precio * $pedido['cantidad'];
            $total += $subtotal;

            $productos[] = [
                'product_id' => (int) $pedido['product_id'],
                'nombre' => SupplierInventory::find($pedido['product_id'])?->product_name ?? 'Producto',
                'cantidad' => (int) $pedido['cantidad'],
                'precio_unitario' => $precio,
                'subtotal' => $subtotal,
            ];
        }

        $dias = (int) $compra->purchase_date->diffInDays(now());
        $fueraDePlazo = $dias > DistributorReturn::DIAS_PLAZO;

        if ($fueraDePlazo && ! $request->boolean('autorizar_fuera_plazo')) {
            return back()->withInput()->with('error',
                "La compra es de hace {$dias} días y el plazo es de " . DistributorReturn::DIAS_PLAZO
                . ' días. Marcá "Autorizar fuera de plazo" si querés hacerla igual.');
        }

        // El cliente tiene ficha, así que el crédito siempre va a su cuenta.
        $destino = DistributorReturn::DESTINO_CUENTA;

        DB::transaction(function () use ($compra, $productos, $total, $datos, $dias, $fueraDePlazo, $destino, &$devolucion) {
            $devolucion = DistributorReturn::create([
                'return_number' => DistributorReturn::siguienteNumero(),
                'distributor_client_id' => $compra->distributor_client_id,
                'distributor_technical_record_id' => $compra->id,
                'origen' => 'technical_record',
                'return_date' => $datos['return_date'],
                'products_returned' => $productos,
                'total_amount' => $total,
                'destino' => $destino,
                'motivo' => $datos['motivo'] ?? null,
                'dias_desde_compra' => $dias,
                'fuera_de_plazo' => $fueraDePlazo,
                'autorizado_por' => $fueraDePlazo ? Auth::id() : null,
                'user_id' => Auth::id(),
                'observations' => $datos['observations'] ?? null,
            ]);

            $this->reintegrarStock($productos);

            DistributorCurrentAccount::create([
                'distributor_client_id' => $compra->distributor_client_id,
                'user_id' => Auth::id(),
                'distributor_technical_record_id' => $compra->id,
                'distributor_return_id' => $devolucion->id,
                'type' => 'credit',
                'amount' => $total,
                'description' => 'Devolución ' . $devolucion->return_number,
                'date' => $datos['return_date'],
                'reference' => $devolucion->return_number,
            ]);
        });

        return redirect()
            ->route('distributor-returns.show', $devolucion)
            ->with('success', 'Devolución ' . $devolucion->return_number . ' registrada. El stock ya volvió al inventario.');
    }

    public function show(DistributorReturn $distributorReturn)
    {
        $distributorReturn->load(['distributorClient', 'clienteNoFrecuente', 'technicalRecord', 'user', 'anuladaPor', 'autorizadaPor']);

        return view('distributor_returns.show', [
            'devolucion' => $distributorReturn,
            'puedeAnular' => Auth::user()?->puedeAnularDevoluciones() ?? false,
        ]);
    }

    /**
     * Anula una devolución: devuelve el stock y compensa la cuenta corriente.
     * El registro no se borra, queda marcado con quién y cuándo lo anuló.
     */
    public function anular(Request $request, DistributorReturn $distributorReturn)
    {
        if (! Auth::user()?->puedeAnularDevoluciones()) {
            return back()->with('error', 'No tenés permiso para anular devoluciones.');
        }

        if ($distributorReturn->estaAnulada()) {
            return back()->with('error', 'Esa devolución ya estaba anulada.');
        }

        $datos = $request->validate([
            'motivo_anulacion' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($distributorReturn, $datos) {
            // Sacar del stock lo que se había reintegrado.
            foreach ($distributorReturn->products_returned as $item) {
                SupplierInventory::where('id', $item['product_id'])
                    ->decrement('stock_quantity', (int) $item['cantidad']);
            }

            // Movimiento compensatorio en vez de borrar el crédito: así queda el
            // rastro de que hubo una devolución y de que se anuló.
            if ($distributorReturn->distributor_client_id) {
                DistributorCurrentAccount::create([
                    'distributor_client_id' => $distributorReturn->distributor_client_id,
                    'user_id' => Auth::id(),
                    'distributor_return_id' => $distributorReturn->id,
                    'type' => 'debt',
                    'amount' => $distributorReturn->total_amount,
                    'description' => 'Anulación de devolución ' . $distributorReturn->return_number,
                    'date' => now()->toDateString(),
                    'reference' => $distributorReturn->return_number . '-ANUL',
                ]);
            }

            $distributorReturn->update([
                'anulada_en' => now(),
                'anulada_por' => Auth::id(),
                'motivo_anulacion' => $datos['motivo_anulacion'],
            ]);
        });

        return back()->with('success', 'Devolución anulada. El stock y la cuenta corriente quedaron como antes.');
    }

    /** Comprobante imprimible. */
    public function comprobante(DistributorReturn $distributorReturn)
    {
        $distributorReturn->load(['distributorClient', 'clienteNoFrecuente', 'user']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('distributor_returns.comprobante', [
            'devolucion' => $distributorReturn,
        ]);

        return $pdf->stream('devolucion-' . $distributorReturn->return_number . '.pdf');
    }

    /** Suma al inventario lo que volvió. */
    private function reintegrarStock(array $productos): void
    {
        foreach ($productos as $item) {
            SupplierInventory::where('id', $item['product_id'])
                ->increment('stock_quantity', (int) $item['cantidad']);
        }
    }

    /** Los productos de una compra, tolerando que el JSON venga como string. */
    private function productosDeCompra($compra): array
    {
        $items = $compra->products_purchased;

        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        return is_array($items) ? $items : [];
    }
}
