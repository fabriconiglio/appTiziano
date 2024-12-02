<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:entrada,salida',
            'description' => 'required'
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Verificar si hay suficiente stock para salidas
        if ($validated['type'] === 'salida' && $product->current_stock < $validated['quantity']) {
            return back()->with('error', 'No hay suficiente stock disponible.');
        }

        // Actualizar el stock del producto
        if ($validated['type'] === 'entrada') {
            $product->current_stock += $validated['quantity'];
        } else {
            $product->current_stock -= $validated['quantity'];
        }

        $product->save();

        // Registrar el movimiento
        $validated['user_id'] = auth()->id();
        StockMovement::create($validated);

        return back()->with('success', 'Movimiento de stock registrado exitosamente.');
    }
}
