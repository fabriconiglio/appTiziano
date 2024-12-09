<!-- resources/views/products/form.blade.php -->
@extends('layouts.app')

@section('title', isset($product) ? 'Editar Producto' : 'Crear Producto')

@section('content')
    <h1>{{ isset($product) ? 'Editar Producto' : 'Agregar Producto' }}</h1>

    <form action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}" method="POST">
        @csrf
        @if(isset($product))
            @method('PUT')
        @endif

        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Precio</label>
            <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $product->price ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">{{ isset($product) ? 'Actualizar' : 'Guardar' }}</button>
    </form>
@endsection
