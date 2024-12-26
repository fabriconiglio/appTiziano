<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\TechnicalRecord;
use Illuminate\Http\Request;

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
        return view('technical_records.create', compact('client'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'hair_type' => 'nullable|string',
            'scalp_condition' => 'nullable|string',
            'current_hair_color' => 'nullable|string',
            'desired_hair_color' => 'nullable|string',
            'hair_treatments' => 'nullable|string',
            'products_used' => 'nullable|array',
            'observations' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048', // Máximo 2MB por imagen
            'next_appointment_notes' => 'nullable|string'
        ]);

        // Procesar las fotos
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('client-photos', 'public');
                $photos[] = $path;
            }
            $validated['photos'] = $photos;
        }

        $validated['client_id'] = $client->id;
        $validated['stylist_id'] = auth()->id();

        TechnicalRecord::create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Ficha técnica creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client, TechnicalRecord $technicalRecord)
    {
        return view('technical_records.show', compact('client', 'technicalRecord'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client, TechnicalRecord $technicalRecord)
    {
        return view('technical_records.edit', compact('client', 'technicalRecord'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client, TechnicalRecord $technicalRecord)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'hair_type' => 'nullable|string',
            'scalp_condition' => 'nullable|string',
            'current_hair_color' => 'nullable|string',
            'desired_hair_color' => 'nullable|string',
            'hair_treatments' => 'nullable|string',
            'products_used' => 'nullable|array',
            'observations' => 'nullable|string',
            'photos.*' => 'nullable|image|max:2048',
            'next_appointment_notes' => 'nullable|string'
        ]);

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

        return redirect()->route('clients.show', $client)
            ->with('success', 'Ficha técnica actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
