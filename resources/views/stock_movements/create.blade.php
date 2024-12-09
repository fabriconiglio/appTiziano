<!-- resources/views/stock_movements/create.blade.php -->
@extends('layouts.app')

@section('title', 'Registrar Movimiento de Stock')

@section('content')
    <h1>Registrar Movimiento de Stock</h1>

    <form action="{{ route('stock-movements.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="product_id" class="form-label">Producto</label>
            <select name="product_id" id="product_id" class="form-control" required>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Cantidad</label>
            <input type="number" name="quantity" id="quantity" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
    </form>
@endsection
