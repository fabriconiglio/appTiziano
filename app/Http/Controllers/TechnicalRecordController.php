<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Product;
use App\Models\TechnicalRecord;
use App\Models\ClientCurrentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TechnicalRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Client $client)
    {
        $products = Product::all();
        return view('technical_records.create', compact('client', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'service_cost' => 'required|numeric|min:0|max:999999.99',
            'hair_type' => 'nullable|string',
            'scalp_condition' => 'nullable|string',
            'current_hair_color' => 'nullable|string',
            'desired_hair_color' => 'nullable|string',
            'hair_treatments' => 'nullable|string',
            'products_used' => 'nullable|array',
            'observations' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048',
            'next_appointment_notes' => 'nullable|string',
            'payment_method' => 'nullable|string|in:efectivo,tarjeta,transferencia,deuda',
            'use_current_account' => 'nullable|boolean'
        ]);

        // MOD-030 (main): Calcular saldo y crear movimientos en cuenta corriente
        $currentBalance = $client->getCurrentBalance();
        $serviceCost = floatval($validated['service_cost']);
        $balanceAdjustment = $currentBalance;
        $finalAmount = max(0, $serviceCost + $balanceAdjustment);

        // Iniciar transacción
        DB::beginTransaction();
        
        try {
            // Procesar las fotos
            if ($request->hasFile('photos')) {
                $photos = [];
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('client-photos', 'public');
                    $photos[] = $path;
                }
                $validated['photos'] = $photos;
            }

            $validated['client_id'] = $client->getKey();
            $validated['stylist_id'] = Auth::id();
            $technicalRecord = TechnicalRecord::create($validated);

            // MOD-030 (main): Crear movimientos en cuenta corriente solo si está marcado el checkbox
            if ($validated['use_current_account'] ?? false) {
                if ($balanceAdjustment != 0) {
                    if ($balanceAdjustment < 0) {
                        // Si el cliente tiene crédito, usar el crédito y crear deuda solo por la diferencia
                        $creditUsed = abs($balanceAdjustment);
                        $remainingDebt = max(0, $serviceCost - $creditUsed);
                        
                        if ($remainingDebt > 0) {
                            ClientCurrentAccount::create([
                                'client_id' => $client->getKey(),
                                'user_id' => Auth::id(),
                                'technical_record_id' => $technicalRecord->id,
                                'type' => 'debt',
                                'amount' => $remainingDebt,
                                'description' => 'Deuda por servicio de peluquería',
                                'date' => now(),
                                'reference' => 'FT-' . $technicalRecord->id,
                                'observations' => "Ficha técnica #{$technicalRecord->id} - Servicio de peluquería - Crédito usado: $" . number_format($creditUsed, 2) . " - Deuda restante: $" . number_format($remainingDebt, 2)
                            ]);
                        }
                    } else {
                        // CORREGIDO: Si el cliente tiene deuda, crear deuda solo por el servicio actual (NO sumar la deuda anterior)
                        ClientCurrentAccount::create([
                            'client_id' => $client->getKey(),
                            'user_id' => Auth::id(),
                            'technical_record_id' => $technicalRecord->id,
                            'type' => 'debt',
                            'amount' => $serviceCost, // CORREGIDO: Solo el costo del servicio, no $finalAmount
                            'description' => 'Deuda por servicio de peluquería',
                            'date' => now(),
                            'reference' => 'FT-' . $technicalRecord->id,
                            'observations' => "Ficha técnica #{$technicalRecord->id} - Servicio de peluquería - Deuda existente: $" . number_format($balanceAdjustment, 2) . " - Nuevo servicio: $" . number_format($serviceCost, 2)
                        ]);
                    }
                } else {
                    // No hay ajuste de cuenta corriente, crear deuda normal
                    if ($serviceCost > 0) {
                        ClientCurrentAccount::create([
                            'client_id' => $client->getKey(),
                            'user_id' => Auth::id(),
                            'technical_record_id' => $technicalRecord->id,
                            'type' => 'debt',
                            'amount' => $serviceCost,
                            'description' => 'Deuda por servicio de peluquería',
                            'date' => now(),
                            'reference' => 'FT-' . $technicalRecord->id,
                            'observations' => "Ficha técnica #{$technicalRecord->id} - Servicio de peluquería"
                        ]);
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('clients.show', $client)
                ->with('success', 'Ficha técnica creada exitosamente.' . 
                    (($validated['use_current_account'] ?? false) ? ' Se registró en la cuenta corriente.' : ' No se registró en la cuenta corriente.'));
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear ficha técnica: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client, TechnicalRecord $technicalRecord)
    {
        // Convertir los IDs de productos en un array
        $productIds = $technicalRecord->products_used ?? [];

        // Obtener los nombres de los productos
        $products = Product::whereIn('id', $productIds)->get();

        return view('technical_records.show', compact('client', 'technicalRecord', 'products'));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client, TechnicalRecord $technicalRecord)
    {
        $products = Product::all();
        
        // MOD-030 (main): Calcular el saldo actual sin incluir esta ficha técnica para evitar duplicación
        $currentBalance = $client->getCurrentBalance();
        // Buscar si esta ficha técnica ya tiene movimientos en cuenta corriente
        $existingMovements = ClientCurrentAccount::where('technical_record_id', $technicalRecord->id)->get();
        $existingDebt = $existingMovements->where('type', 'debt')->sum('amount');
        $existingPayments = $existingMovements->where('type', 'payment')->sum('amount');
        $existingNetAmount = $existingDebt - $existingPayments;
        
        // Calcular saldo sin esta ficha técnica
        $currentBalanceWithoutThisRecord = $currentBalance - $existingNetAmount;
        
        return view('technical_records.edit', compact('client', 'technicalRecord', 'products', 'currentBalanceWithoutThisRecord'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client, TechnicalRecord $technicalRecord)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'service_cost' => 'required|numeric|min:0|max:999999.99',
            'hair_type' => 'nullable|string',
            'scalp_condition' => 'nullable|string',
            'current_hair_color' => 'nullable|string',
            'desired_hair_color' => 'nullable|string',
            'hair_treatments' => 'nullable|string',
            'products_used' => 'nullable|array',
            'observations' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048',
            'next_appointment_notes' => 'nullable|string',
            'payment_method' => 'nullable|string|in:efectivo,tarjeta,transferencia,deuda',
            'use_current_account' => 'nullable|boolean'
        ]);

        // MOD-030 (main): Calcular saldo sin esta ficha técnica y nuevo monto
        $currentBalance = $client->getCurrentBalance();
        $existingMovements = ClientCurrentAccount::where('technical_record_id', $technicalRecord->id)->get();
        $existingDebt = $existingMovements->where('type', 'debt')->sum('amount');
        $existingPayments = $existingMovements->where('type', 'payment')->sum('amount');
        $existingNetAmount = $existingDebt - $existingPayments;
        $currentBalanceWithoutThisRecord = $currentBalance - $existingNetAmount;
        
        $serviceCost = floatval($validated['service_cost']);
        $balanceAdjustment = $currentBalanceWithoutThisRecord;
        $finalAmount = max(0, $serviceCost + $balanceAdjustment);

        // Iniciar transacción
        DB::beginTransaction();
        
        try {
            // Procesar nuevas fotos
            if ($request->hasFile('photos')) {
                $photos = $technicalRecord->photos ?? [];
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('client-photos', 'public');
                    $photos[] = $path;
                }
                $validated['photos'] = $photos;
            }

            $technicalRecord->update($validated);

            // MOD-030 (main): Actualizar movimientos en cuenta corriente
            // Eliminar movimientos existentes para recrearlos
            foreach ($existingMovements as $movement) {
                $movement->delete();
            }

            // Crear movimientos en cuenta corriente solo si está marcado el checkbox
            if ($validated['use_current_account'] ?? false) {
                if ($balanceAdjustment != 0) {
                    if ($balanceAdjustment < 0) {
                        // Si el cliente tiene crédito, usar el crédito y crear deuda solo por la diferencia
                        $creditUsed = abs($balanceAdjustment);
                        $remainingDebt = max(0, $serviceCost - $creditUsed);
                        
                        if ($remainingDebt > 0) {
                            ClientCurrentAccount::create([
                                'client_id' => $client->getKey(),
                                'user_id' => Auth::id(),
                                'technical_record_id' => $technicalRecord->id,
                                'type' => 'debt',
                                'amount' => $remainingDebt,
                                'description' => 'Deuda por servicio de peluquería',
                                'date' => now(),
                                'reference' => 'FT-' . $technicalRecord->id,
                                'observations' => "Ficha técnica #{$technicalRecord->id} - Servicio de peluquería - Crédito usado: $" . number_format($creditUsed, 2) . " - Deuda restante: $" . number_format($remainingDebt, 2)
                            ]);
                        }
                    } else {
                        // CORREGIDO: Si el cliente tiene deuda, crear deuda solo por el servicio actual (NO sumar la deuda anterior)
                        ClientCurrentAccount::create([
                            'client_id' => $client->getKey(),
                            'user_id' => Auth::id(),
                            'technical_record_id' => $technicalRecord->id,
                            'type' => 'debt',
                            'amount' => $serviceCost, // CORREGIDO: Solo el costo del servicio, no $finalAmount
                            'description' => 'Deuda por servicio de peluquería',
                            'date' => now(),
                            'reference' => 'FT-' . $technicalRecord->id,
                            'observations' => "Ficha técnica #{$technicalRecord->id} - Servicio de peluquería - Deuda existente: $" . number_format($balanceAdjustment, 2) . " - Nuevo servicio: $" . number_format($serviceCost, 2)
                        ]);
                    }
                } else {
                    // No hay ajuste de cuenta corriente, crear deuda normal
                    if ($serviceCost > 0) {
                        ClientCurrentAccount::create([
                            'client_id' => $client->getKey(),
                            'user_id' => Auth::id(),
                            'technical_record_id' => $technicalRecord->id,
                            'type' => 'debt',
                            'amount' => $serviceCost,
                            'description' => 'Deuda por servicio de peluquería',
                            'date' => now(),
                            'reference' => 'FT-' . $technicalRecord->id,
                            'observations' => "Ficha técnica #{$technicalRecord->id} - Servicio de peluquería"
                        ]);
                    }
                }
            }
            
            DB::commit();
            return redirect()->route('clients.show', $client)
                ->with('success', 'Ficha técnica actualizada exitosamente.' . 
                    (($validated['use_current_account'] ?? false) ? ' Se registró en la cuenta corriente.' : ' No se registró en la cuenta corriente.'));
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al actualizar ficha técnica: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client, TechnicalRecord $technicalRecord)
    {
        $technicalRecord->delete();
        return redirect()->route('clients.show', $client)
            ->with('success', 'Ficha técnica eliminada exitosamente.');
    }

    public function deletePhoto(Request $request, Client $client, TechnicalRecord $technicalRecord)
    {
        try {
            $photo = $request->input('photo');

            // Verificar que la foto existe en el array
            if (!in_array($photo, $technicalRecord->photos ?? [])) {
                return response()->json(['message' => 'Foto no encontrada'], 404);
            }

            // Eliminar el archivo físico
            if (Storage::exists('public/' . $photo)) {
                Storage::delete('public/' . $photo);
            }

            // Actualizar el array de fotos
            $photos = array_values(array_filter($technicalRecord->photos ?? [], function($p) use ($photo) {
                return $p !== $photo;
            }));

            // Actualizar el registro
            $technicalRecord->update(['photos' => $photos]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar foto: ' . $e->getMessage());
            return response()->json(['message' => 'Error al eliminar la foto'], 500);
        }
    }
}
