<?php

namespace App\Http\Controllers;

use App\Models\HairdressingSupplier;
use App\Models\HairdressingSupplierCurrentAccount;
use App\Models\HairdressingSupplierPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HairdressingSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener todos los proveedores con sus compras para calcular deuda
        $query = HairdressingSupplier::with('hairdressingSupplierPurchases')->orderBy('name');

        // Aplicar filtro de búsqueda si se proporciona
        if ($request->has('search') && !empty($request->get('search'))) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('business_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('contact_person', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('cuit', 'LIKE', "%{$searchTerm}%");
            });
        }

        $suppliers = $query->paginate(15);

        // Proveedores desactivados para mostrar en la vista
        $inactiveSuppliers = HairdressingSupplier::where('is_active', false)->get();

        return view('hairdressing-suppliers.index', compact('suppliers', 'inactiveSuppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hairdressing-suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'cuit' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_time' => 'nullable|string|max:255',
            'minimum_order' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'bank_account' => 'nullable|string|max:255',
            'tax_category' => 'nullable|string|max:100'
        ]);

        HairdressingSupplier::create($validated);

        return redirect()->route('hairdressing-suppliers.index')
            ->with('success', 'Proveedor de peluquería creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(HairdressingSupplier $hairdressingSupplier)
    {
        $hairdressingSupplier->load(['products', 'hairdressingSupplierPurchases']);
        
        $stats = [
            'total_products' => $hairdressingSupplier->products_count,
            'total_value' => $hairdressingSupplier->total_inventory_value,
            'low_stock_products' => $hairdressingSupplier->low_stock_products->count(),
            'out_of_stock_products' => $hairdressingSupplier->out_of_stock_products->count(),
        ];

        return view('hairdressing-suppliers.show', compact('hairdressingSupplier', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HairdressingSupplier $hairdressingSupplier)
    {
        return view('hairdressing-suppliers.edit', compact('hairdressingSupplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HairdressingSupplier $hairdressingSupplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'cuit' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_time' => 'nullable|string|max:255',
            'minimum_order' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'bank_account' => 'nullable|string|max:255',
            'tax_category' => 'nullable|string|max:100'
        ]);

        $hairdressingSupplier->update($validated);

        return redirect()->route('hairdressing-suppliers.index')
            ->with('success', 'Proveedor de peluquería actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HairdressingSupplier $hairdressingSupplier)
    {
        $hairdressingSupplier->delete();

        return redirect()->route('hairdressing-suppliers.index')
            ->with('success', 'Proveedor de peluquería eliminado exitosamente.');
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(HairdressingSupplier $hairdressingSupplier)
    {
        $hairdressingSupplier->update(['is_active' => !$hairdressingSupplier->is_active]);

        $status = $hairdressingSupplier->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "Proveedor de peluquería {$status} exitosamente.");
    }

    /**
     * Restore deleted supplier
     */
    public function restore($id)
    {
        $supplier = HairdressingSupplier::withTrashed()->findOrFail($id);
        $supplier->restore();

        return redirect()->back()->with('success', 'Proveedor de peluquería restaurado exitosamente.');
    }

    /**
     * Mostrar formulario para crear una nueva compra
     */
    public function createPurchase(HairdressingSupplier $hairdressingSupplier)
    {
        return view('hairdressing-suppliers.create-purchase', compact('hairdressingSupplier'));
    }

    /**
     * Almacenar una nueva compra del proveedor de peluquería
     */
    public function storePurchase(Request $request, HairdressingSupplier $hairdressingSupplier)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'receipt_number' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'payment_amount' => 'required|numeric|min:0',
            'balance_amount' => 'nullable|numeric',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'notes' => 'nullable|string',
            'use_available_credit' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            // Obtener crédito disponible del proveedor
            $availableCredit = $hairdressingSupplier->getAvailableCredit();
            $useCredit = $validated['use_available_credit'] ?? true;
            
            // Calcular montos considerando crédito disponible
            $totalAmount = $validated['total_amount'];
            $paymentAmount = $validated['payment_amount'];
            
            if ($useCredit && $availableCredit > 0) {
                // Aplicar crédito disponible
                $creditToUse = min($availableCredit, $totalAmount);
                $remainingAmount = $totalAmount - $creditToUse;
                
                // El pago solo se aplica al monto restante después del crédito
                $finalPaymentAmount = min($paymentAmount, $remainingAmount);
                
                // Si el pago es mayor al monto restante después del crédito, generar nuevo crédito
                if ($paymentAmount > $remainingAmount) {
                    $newCredit = $paymentAmount - $remainingAmount;
                } else {
                    $newCredit = 0;
                }
            } else {
                // No usar crédito, procesar normalmente
                $creditToUse = 0;
                $remainingAmount = $totalAmount;
                $finalPaymentAmount = min($paymentAmount, $remainingAmount);
                $newCredit = max(0, $paymentAmount - $remainingAmount);
            }

            // Procesar el archivo de la factura si se proporciona uno
            if ($request->hasFile('receipt_file')) {
                $filePath = $request->file('receipt_file')->store('hairdressing-supplier-receipts', 'public');
                $validated['receipt_file'] = $filePath;
            } else {
                $validated['receipt_file'] = null;
            }

            // Calcular el saldo pendiente final
            $finalBalanceAmount = $remainingAmount - $finalPaymentAmount;
            
            // Agregar el ID del proveedor y usuario
            $validated['hairdressing_supplier_id'] = $hairdressingSupplier->id;
            $validated['user_id'] = \Illuminate\Support\Facades\Auth::id();
            $validated['total_amount'] = $totalAmount;
            $validated['payment_amount'] = $finalPaymentAmount;
            $validated['balance_amount'] = $finalBalanceAmount;

            // Crear la compra en la base de datos
            $purchase = \App\Models\HairdressingSupplierPurchase::create($validated);

            // Crear movimientos en cuenta corriente
            if ($totalAmount > 0) {
                // Crear movimiento de deuda por el total de la compra
                \App\Models\HairdressingSupplierCurrentAccount::create([
                    'hairdressing_supplier_id' => $hairdressingSupplier->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'hairdressing_supplier_purchase_id' => $purchase->id,
                    'type' => 'debt',
                    'amount' => $totalAmount,
                    'description' => 'Deuda por compra - Factura ' . $validated['receipt_number'],
                    'date' => $validated['purchase_date'],
                    'reference' => 'COMP-' . $purchase->id,
                    'observations' => $validated['notes']
                ]);
            }

            if ($finalPaymentAmount > 0) {
                // Crear movimiento de pago
                \App\Models\HairdressingSupplierCurrentAccount::create([
                    'hairdressing_supplier_id' => $hairdressingSupplier->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'hairdressing_supplier_purchase_id' => $purchase->id,
                    'type' => 'payment',
                    'amount' => $finalPaymentAmount,
                    'description' => 'Pago por compra - Factura ' . $validated['receipt_number'],
                    'date' => $validated['purchase_date'],
                    'reference' => 'PAGO-' . $purchase->id,
                    'observations' => $validated['notes']
                ]);
            }

            // Si se usó crédito disponible, eliminar los movimientos de crédito más antiguos para reflejar el uso
            if ($useCredit && $creditToUse > 0) {
                // Obtener los movimientos de crédito ordenados por fecha (más antiguos primero)
                $creditMovements = \App\Models\HairdressingSupplierCurrentAccount::where('hairdressing_supplier_id', $hairdressingSupplier->id)
                    ->where('type', 'credit')
                    ->orderBy('date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $remainingCreditToUse = $creditToUse;
                
                // Eliminar los créditos más antiguos hasta cubrir el monto usado
                foreach ($creditMovements as $creditMovement) {
                    if ($remainingCreditToUse <= 0) {
                        break;
                    }
                    
                    if ($creditMovement->amount <= $remainingCreditToUse) {
                        // El crédito completo se usa, eliminarlo
                        $remainingCreditToUse -= $creditMovement->amount;
                        $creditMovement->delete();
                    } else {
                        // Solo se usa parte del crédito, reducir su monto
                        $creditMovement->amount -= $remainingCreditToUse;
                        $creditMovement->save();
                        $remainingCreditToUse = 0;
                    }
                }
                
                // Crear movimiento de pago para reflejar el uso del crédito
                \App\Models\HairdressingSupplierCurrentAccount::create([
                    'hairdressing_supplier_id' => $hairdressingSupplier->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'hairdressing_supplier_purchase_id' => $purchase->id,
                    'type' => 'payment',
                    'amount' => $creditToUse,
                    'description' => 'Uso de crédito disponible - Factura ' . $validated['receipt_number'],
                    'date' => $validated['purchase_date'],
                    'reference' => 'CREDIT-USE-' . $purchase->id,
                    'observations' => 'Crédito disponible aplicado a esta compra'
                ]);
            }

            if ($newCredit > 0) {
                // Crear movimiento de crédito (saldo a favor)
                \App\Models\HairdressingSupplierCurrentAccount::create([
                    'hairdressing_supplier_id' => $hairdressingSupplier->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'hairdressing_supplier_purchase_id' => $purchase->id,
                    'type' => 'credit',
                    'amount' => $newCredit,
                    'description' => 'Excedente a favor - Factura ' . $validated['receipt_number'],
                    'date' => $validated['purchase_date'],
                    'reference' => 'CREDIT-' . $purchase->id,
                    'observations' => 'Pago excedente que genera saldo a favor'
                ]);
            }

            DB::commit();

            $message = 'Compra registrada exitosamente.';
            if ($useCredit && $availableCredit > 0) {
                $message .= " Se aplicó crédito de $" . number_format($creditToUse, 2) . ".";
            }
            if ($newCredit > 0) {
                $message .= " Se generó saldo a favor de $" . number_format($newCredit, 2) . ".";
            }

            return redirect()->route('hairdressing-suppliers.show', $hairdressingSupplier)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear compra de proveedor de peluquería: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al registrar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario para editar una compra
     */
    public function editPurchase(HairdressingSupplier $hairdressingSupplier, $purchase)
    {
        $purchase = \App\Models\HairdressingSupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->hairdressing_supplier_id !== $hairdressingSupplier->id) {
            abort(404);
        }
        
        return view('hairdressing-suppliers.edit-purchase', compact('hairdressingSupplier', 'purchase'));
    }

    /**
     * Actualizar una compra existente
     */
    public function updatePurchase(Request $request, HairdressingSupplier $hairdressingSupplier, $purchase)
    {
        $purchase = \App\Models\HairdressingSupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->hairdressing_supplier_id !== $hairdressingSupplier->id) {
            abort(404);
        }

        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'receipt_number' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'payment_amount' => 'required|numeric|min:0',
            'balance_amount' => 'nullable|numeric',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'notes' => 'nullable|string',
            'use_available_credit' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            // Al editar una compra, NO usar crédito disponible ya que puede provenir de la misma compra
            // Los movimientos se recrearán completamente basándose solo en los montos ingresados
            $useCredit = false;
            $creditToUse = 0;
            
            // Calcular montos sin considerar crédito disponible
            $totalAmount = $validated['total_amount'];
            $paymentAmount = $validated['payment_amount'];
            $remainingAmount = $totalAmount;
            $finalPaymentAmount = min($paymentAmount, $remainingAmount);
            $newCredit = max(0, $paymentAmount - $remainingAmount);

            // Procesar el archivo de la factura si se proporciona uno nuevo
            if ($request->hasFile('receipt_file')) {
                // Eliminar archivo anterior si existe
                if ($purchase->receipt_file && Storage::exists('public/' . $purchase->receipt_file)) {
                    Storage::delete('public/' . $purchase->receipt_file);
                }
                
                $filePath = $request->file('receipt_file')->store('hairdressing-supplier-receipts', 'public');
                $validated['receipt_file'] = $filePath;
            } else {
                // Mantener el archivo actual
                $validated['receipt_file'] = $purchase->receipt_file;
            }

            // Calcular el saldo pendiente final
            $finalBalanceAmount = $remainingAmount - $finalPaymentAmount;
            
            // Actualizar los campos de la compra
            $validated['total_amount'] = $totalAmount;
            $validated['payment_amount'] = $finalPaymentAmount;
            $validated['balance_amount'] = $finalBalanceAmount;

            // Actualizar la compra
            $purchase->update($validated);

            // Eliminar los movimientos de cuenta corriente antiguos relacionados con esta compra
            \App\Models\HairdressingSupplierCurrentAccount::where('hairdressing_supplier_purchase_id', $purchase->id)->delete();

            // Recrear los movimientos de cuenta corriente con los nuevos valores
            if ($totalAmount > 0) {
                // Crear movimiento de deuda por el total de la compra
                \App\Models\HairdressingSupplierCurrentAccount::create([
                    'hairdressing_supplier_id' => $hairdressingSupplier->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'hairdressing_supplier_purchase_id' => $purchase->id,
                    'type' => 'debt',
                    'amount' => $totalAmount,
                    'description' => 'Deuda por compra - Factura ' . $validated['receipt_number'],
                    'date' => $validated['purchase_date'],
                    'reference' => 'COMP-' . $purchase->id,
                    'observations' => $validated['notes'] ?? null
                ]);
            }

            if ($finalPaymentAmount > 0) {
                // Crear movimiento de pago
                \App\Models\HairdressingSupplierCurrentAccount::create([
                    'hairdressing_supplier_id' => $hairdressingSupplier->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'hairdressing_supplier_purchase_id' => $purchase->id,
                    'type' => 'payment',
                    'amount' => $finalPaymentAmount,
                    'description' => 'Pago por compra - Factura ' . $validated['receipt_number'],
                    'date' => $validated['purchase_date'],
                    'reference' => 'PAGO-' . $purchase->id,
                    'observations' => $validated['notes'] ?? null
                ]);
            }

            if ($newCredit > 0) {
                // Crear movimiento de crédito (saldo a favor)
                \App\Models\HairdressingSupplierCurrentAccount::create([
                    'hairdressing_supplier_id' => $hairdressingSupplier->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'hairdressing_supplier_purchase_id' => $purchase->id,
                    'type' => 'credit',
                    'amount' => $newCredit,
                    'description' => 'Excedente a favor - Factura ' . $validated['receipt_number'],
                    'date' => $validated['purchase_date'],
                    'reference' => 'CREDIT-' . $purchase->id,
                    'observations' => 'Pago excedente que genera saldo a favor'
                ]);
            }

            DB::commit();
            
            $message = 'Compra actualizada exitosamente.';
            if ($newCredit > 0) {
                $message .= " Se generó saldo a favor de $" . number_format($newCredit, 2) . ".";
            }

            return redirect()->route('hairdressing-suppliers.show', $hairdressingSupplier)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar compra de proveedor de peluquería: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al actualizar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar una compra del proveedor de peluquería
     */
    public function destroyPurchase(HairdressingSupplier $hairdressingSupplier, $purchase)
    {
        $purchase = \App\Models\HairdressingSupplierPurchase::findOrFail($purchase);
        
        // Verificar que la compra pertenece al proveedor
        if ($purchase->hairdressing_supplier_id !== $hairdressingSupplier->id) {
            abort(404);
        }

        DB::beginTransaction();
        
        try {
            // Eliminar los movimientos de cuenta corriente relacionados con esta compra
            \App\Models\HairdressingSupplierCurrentAccount::where('hairdressing_supplier_purchase_id', $purchase->id)->delete();

            // Eliminar el archivo de la factura si existe
            if ($purchase->receipt_file && Storage::exists('public/' . $purchase->receipt_file)) {
                Storage::delete('public/' . $purchase->receipt_file);
            }

            // Eliminar la compra
            $purchase->delete();
            
            DB::commit();
            
            return redirect()->route('hairdressing-suppliers.show', $hairdressingSupplier)
                ->with('success', 'Compra eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('hairdressing-suppliers.show', $hairdressingSupplier)
                ->with('error', 'Error al eliminar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Buscar el total de una factura existente por número y proveedor
     */
    public function getReceiptTotal(Request $request, HairdressingSupplier $hairdressingSupplier)
    {
        $receiptNumber = $request->input('receipt_number');
        
        if (!$receiptNumber) {
            return response()->json(['error' => 'Número de factura requerido'], 400);
        }

        // Buscar la factura más reciente que tenga saldo pendiente
        $purchase = HairdressingSupplierPurchase::where('hairdressing_supplier_id', $hairdressingSupplier->id)
            ->where('receipt_number', $receiptNumber)
            ->where('balance_amount', '>', 0) // Solo facturas con saldo pendiente
            ->orderBy('created_at', 'desc') // La más reciente primero
            ->first();

        if ($purchase) {
            return response()->json([
                'success' => true,
                'total_amount' => $purchase->total_amount,
                'balance_amount' => $purchase->balance_amount,
                'payment_amount' => $purchase->payment_amount,
                'purchase_date' => $purchase->purchase_date->format('d/m/Y'),
                'message' => 'Factura encontrada'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se encontró una factura con ese número para este proveedor'
        ]);
    }

    /**
     * Mostrar historial de cuenta corriente del proveedor de peluquería
     * Con saldo acumulado (running balance) por fila
     */
    public function showCurrentAccount(HairdressingSupplier $hairdressingSupplier)
    {
        // Ordenar cronológicamente (ASC) para calcular saldo acumulado
        $currentAccounts = $hairdressingSupplier->currentAccounts()
            ->with(['user', 'hairdressingSupplierPurchase'])
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Calcular saldo acumulado por fila (running balance)
        $runningBalance = 0;
        foreach ($currentAccounts as $account) {
            if ($account->type === 'debt') {
                $runningBalance += $account->amount;
            } else {
                // payment o credit restan del saldo
                $runningBalance -= $account->amount;
            }
            $account->running_balance = $runningBalance;
        }

        // Totales separados para el resumen
        $totalDebts = HairdressingSupplierCurrentAccount::getTotalDebts($hairdressingSupplier->id);
        $totalPayments = HairdressingSupplierCurrentAccount::getTotalPayments($hairdressingSupplier->id);
        $totalCredits = HairdressingSupplierCurrentAccount::getTotalCredits($hairdressingSupplier->id);

        $currentBalance = $hairdressingSupplier->getCurrentBalance();
        $formattedBalance = $hairdressingSupplier->getFormattedBalance();

        return view('hairdressing_supplier_current_accounts.show', compact(
            'hairdressingSupplier',
            'currentAccounts',
            'currentBalance',
            'formattedBalance',
            'totalDebts',
            'totalPayments',
            'totalCredits'
        ));
    }

    /**
     * Mostrar formulario para registrar un pago independiente
     */
    public function createPayment(HairdressingSupplier $hairdressingSupplier)
    {
        $currentBalance = $hairdressingSupplier->getCurrentBalance();
        $formattedBalance = $hairdressingSupplier->getFormattedBalance();

        return view('hairdressing-suppliers.create-payment', compact(
            'hairdressingSupplier',
            'currentBalance',
            'formattedBalance'
        ));
    }

    /**
     * Registrar un pago independiente al proveedor de peluquería
     */
    public function storePayment(Request $request, HairdressingSupplier $hairdressingSupplier)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'observations' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $currentBalance = $hairdressingSupplier->getCurrentBalance();

            // Crear movimiento de pago en la cuenta corriente
            HairdressingSupplierCurrentAccount::create([
                'hairdressing_supplier_id' => $hairdressingSupplier->id,
                'user_id' => Auth::id(),
                'hairdressing_supplier_purchase_id' => null,
                'type' => 'payment',
                'amount' => $validated['amount'],
                'description' => 'Pago a proveedor' . ($validated['reference'] ? ' - Ref: ' . $validated['reference'] : ''),
                'date' => $validated['payment_date'],
                'reference' => $validated['reference'] ?? 'PAGO-IND-' . time(),
                'observations' => $validated['observations'],
            ]);

            DB::commit();

            $newBalance = $hairdressingSupplier->getCurrentBalance();
            $message = 'Pago registrado exitosamente.';

            if ($newBalance < 0) {
                $message .= ' Excedente a favor: $' . number_format(abs($newBalance), 2) . '.';
            } elseif ($newBalance > 0) {
                $message .= ' Deuda pendiente: $' . number_format($newBalance, 2) . '.';
            } else {
                $message .= ' Cuenta al día.';
            }

            return redirect()->route('hairdressing-suppliers.show', $hairdressingSupplier)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar pago a proveedor de peluquería: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }
}
