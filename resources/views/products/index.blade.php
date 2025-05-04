@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Productos</h1>
    <div class="btn-group">
        <a href="{{ route('products.create') }}" class="btn btn-primary me-2">
            <i class="fas fa-plus"></i> Agregar Producto
        </a>
        <a href="{{ route('categories.create') }}" class="btn btn-success me-2">
            <i class="fas fa-folder-plus"></i> Agregar Categoría
        </a>
        <a href="{{ route('brands.create') }}" class="btn btn-info">
            <i class="fas fa-trademark"></i> Agregar Marca
        </a>
    </div>
</div>

    @if ($products->isEmpty())
        <div class="alert alert-warning">No hay productos registrados.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>SKU</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product->name }}</td>
                        <td>
                            <code>{{ $product->sku ?: 'N/A' }}</code>
                        </td>
                        <td>
                            @if($product->category)
                                <span class="badge bg-primary">{{ $product->category->name }}</span>
                            @else
                                <span class="badge bg-secondary">Sin categoría</span>
                            @endif
                        </td>
                        <td>
                            @if($product->brand)
                                <span class="badge bg-info text-dark">{{ $product->brand->name }}</span>
                            @else
                                <span class="badge bg-secondary">Sin marca</span>
                            @endif
                        </td>
                        <td>
                            @if($product->current_stock <= $product->minimum_stock)
                                <span class="text-danger fw-bold">
                                    {{ $product->current_stock }}
                                </span>
                            @else
                                {{ $product->current_stock }}
                            @endif
                            <small class="text-muted">/ Min: {{ $product->minimum_stock ?: '0' }}</small>
                        </td>
                        <td class="text-end">${{ number_format($product->price, 2) }}</td>
                        <td>
                            @if($product->description)
                                {{ Str::limit($product->description, 50) }}
                            @else
                                <span class="text-muted">Sin descripción</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('products.edit', $product) }}"
                                   class="btn btn-sm btn-warning"
                                   title="Editar producto">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <button type="button"
                                        class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        title="Eliminar producto">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar el producto <strong id="productName"></strong>?</p>
                    <form id="deleteForm" method="POST" action="{{ route('products.destroy', '') }}">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const productId = button.getAttribute('data-id');
        const productName = button.getAttribute('data-name');

        const form = deleteModal.querySelector('#deleteForm');
        form.action = form.action + '/' + productId;

        deleteModal.querySelector('#productName').textContent = productName;
    });
});
</script>
@endpush
