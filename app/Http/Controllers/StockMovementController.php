<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function create()
    {
        // Obtén todos los productos para llenar el select
        $products = Product::all();

        // Retorna la vista 'stock_movements.create' y pasa los productos
        return view('stock_movements.create', compact('products'));
    }

    public function store(Request $request)
    {

        // Validación de los datos
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:entrada,salida',
            'description' => 'required|string|max:255',
        ]);

        // Obtén el producto correspondiente
        $product = Product::findOrFail($validated['product_id']);

        // Verifica el stock en caso de una salida
        if ($validated['type'] === 'salida' && $product->current_stock < $validated['quantity']) {
            return redirect()->back()->with('error', 'No hay suficiente stock disponible.');
        }

        // Actualiza el stock del producto
        if ($validated['type'] === 'entrada') {
            $product->current_stock += $validated['quantity'];
        } else {
            $product->current_stock -= $validated['quantity'];
        }

        $product->save();

        // Crea el movimiento de stock
        $validated['user_id'] = auth()->id();
        StockMovement::create($validated);

        // Redirige a la vista 'create' con un mensaje de éxito
        return redirect()->route('stock-movements.create')->with('success', 'Movimiento de stock registrado exitosamente.');
    }
}

