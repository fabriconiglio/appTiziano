<!-- resources/views/stock_movements/create.blade.php -->
@extends('layouts.app')

@section('title', 'Registrar Movimiento de Stock')

@section('content')
    <div class="container">
        <h1 class="mb-4">Registrar Movimiento de Stock</h1>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('stock-movements.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="product_id" class="form-label">Producto</label>
                <select name="product_id" id="product_id" class="form-control" required>
                    <option value="">Seleccionar producto</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (Stock actual: {{ $product->current_stock }})</option>
                    @endforeach
                </select>
                @error('product_id')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">Tipo de Movimiento</label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                </select>
                @error('type')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Cantidad</label>
                <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                @error('quantity')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <input type="text" name="description" id="description" class="form-control"
                       maxlength="255"
                       placeholder="Ej: Compra de productos, Venta a cliente, etc."
                       required>
                @error('description')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i>
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>
                    Registrar Movimiento
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Auto-cerrar las alertas después de 5 segundos
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    var alerts = document.querySelectorAll('.alert');
                    alerts.forEach(function(alert) {
                        var bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });

            // Validación del lado del cliente
            document.querySelector('form').addEventListener('submit', function(e) {
                const type = document.getElementById('type').value;
                const quantity = document.getElementById('quantity').value;
                const productSelect = document.getElementById('product_id');
                const selectedOption = productSelect.options[productSelect.selectedIndex];

                if (type === 'salida') {
                    const currentStock = parseInt(selectedOption.textContent.match(/Stock actual: (\d+)/)[1]);
                    if (parseInt(quantity) > currentStock) {
                        e.preventDefault();
                        alert('No hay suficiente stock disponible para realizar esta salida.');
                    }
                }
            });
        </script>
    @endpush
@endsection
