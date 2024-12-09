@extends('layouts.app')

@section('title', 'Crear Producto')

@section('content')
    <div class="container">
        <h1 class="mb-4">Crear Producto</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>¡Error!</strong> Por favor corrige los siguientes errores:
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
                <label for="sku" class="form-label">SKU</label>
                <input type="text" name="sku" id="sku" class="form-control" value="{{ old('sku') }}" required>
            </div>

            <div class="mb-3">
                <label for="current_stock" class="form-label">Stock Actual</label>
                <input type="number" name="current_stock" id="current_stock" class="form-control" value="{{ old('current_stock') }}" required>
            </div>

            <div class="mb-3">
                <label for="minimum_stock" class="form-label">Stock Mínimo</label>
                <input type="number" name="minimum_stock" id="minimum_stock" class="form-control" value="{{ old('minimum_stock') }}" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Precio</label>
                <input type="number" step="0.01" name="price" id="price" class="form-control" value="{{ old('price') }}" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <textarea name="description" id="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Guardar Producto</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
@endsection
