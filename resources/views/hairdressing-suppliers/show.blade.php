@extends('layouts.app')

@section('title', 'Detalles del Proveedor de Peluquería')

@section('content')
<div class="container">
    <div class="row">
        <!-- Información Principal -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $hairdressingSupplier->name }}</h4>
                    <div>
                        <a href="{{ route('hairdressing-suppliers.edit', $hairdressingSupplier) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('hairdressing-suppliers.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Información Básica -->
                    <div class="row mb-4">
                        <h5>Información Básica</h5>
                        <hr>
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> {{ $hairdressingSupplier->name }}</p>
                            @if($hairdressingSupplier->business_name)
                                <p><strong>Razón Social:</strong> {{ $hairdressingSupplier->business_name }}</p>
                            @endif
                            @if($hairdressingSupplier->cuit)
                                <p><strong>CUIT:</strong> {{ $hairdressingSupplier->cuit }}</p>
                            @endif
                            @if($hairdressingSupplier->tax_category)
                                <p><strong>Categoría Fiscal:</strong> {{ $hairdressingSupplier->tax_category }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado:</strong> 
                                <span class="badge {{ $hairdressingSupplier->status_badge_class }}">
                                    {{ $hairdressingSupplier->status_text }}
                                </span>
                            </p>
                            <p><strong>Fecha de Registro:</strong> {{ $hairdressingSupplier->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Última Actualización:</strong> {{ $hairdressingSupplier->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="row mb-4">
                        <h5>Información de Contacto</h5>
                        <hr>
                        <div class="col-md-6">
                            @if($hairdressingSupplier->contact_person)
                                <p><strong>Persona de Contacto:</strong> {{ $hairdressingSupplier->contact_person }}</p>
                            @endif
                            @if($hairdressingSupplier->email)
                                <p><strong>Email:</strong> <a href="mailto:{{ $hairdressingSupplier->email }}">{{ $hairdressingSupplier->email }}</a></p>
                            @endif
                            @if($hairdressingSupplier->phone)
                                <p><strong>Teléfono:</strong> <a href="tel:{{ $hairdressingSupplier->phone }}">{{ $hairdressingSupplier->phone }}</a></p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($hairdressingSupplier->website)
                                <p><strong>Sitio Web:</strong> <a href="{{ $hairdressingSupplier->website }}" target="_blank">{{ $hairdressingSupplier->website }}</a></p>
                            @endif
                            @if($hairdressingSupplier->address)
                                <p><strong>Dirección:</strong> {{ $hairdressingSupplier->address }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Información Comercial -->
                    <div class="row mb-4">
                        <h5>Información Comercial</h5>
                        <hr>
                        <div class="col-md-6">
                            @if($hairdressingSupplier->payment_terms)
                                <p><strong>Condiciones de Pago:</strong> {{ $hairdressingSupplier->payment_terms }}</p>
                            @endif
                            @if($hairdressingSupplier->delivery_time)
                                <p><strong>Tiempo de Entrega:</strong> {{ $hairdressingSupplier->delivery_time }}</p>
                            @endif
                            @if($hairdressingSupplier->minimum_order)
                                <p><strong>Pedido Mínimo:</strong> ${{ number_format($hairdressingSupplier->minimum_order, 2) }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($hairdressingSupplier->discount_percentage)
                                <p><strong>Descuento:</strong> {{ $hairdressingSupplier->discount_percentage }}%</p>
                            @endif
                            @if($hairdressingSupplier->bank_account)
                                <p><strong>Cuenta Bancaria:</strong> {{ $hairdressingSupplier->bank_account }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Notas -->
                    @if($hairdressingSupplier->notes)
                        <div class="row mb-4">
                            <h5>Notas</h5>
                            <hr>
                            <div class="col-12">
                                <p>{{ $hairdressingSupplier->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Productos Asociados -->
                    <div class="row">
                        <h5>Productos Asociados</h5>
                        <hr>
                        <div class="col-12">
                            @if($hairdressingSupplier->products->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Categoría</th>
                                                <th>Precio</th>
                                                <th>Stock</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hairdressingSupplier->products as $product)
                                                <tr>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->category ? $product->category->name : '-' }}</td>
                                                    <td>${{ number_format($product->price, 2) }}</td>
                                                    <td>{{ $product->current_stock }}</td>
                                                    <td>
                                                        <span class="badge {{ $product->status_badge_class }}">
                                                            {{ $product->status_text }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No hay productos asociados a este proveedor.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar con Estadísticas -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Estadísticas</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-1">{{ $stats['total_products'] }}</h4>
                                <small class="text-muted">Productos</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">${{ number_format($stats['total_value'], 2) }}</h4>
                                <small class="text-muted">Valor Total</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">{{ $stats['low_stock_products'] }}</h4>
                                <small class="text-muted">Bajo Stock</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3">
                                <h4 class="text-danger mb-1">{{ $stats['out_of_stock_products'] }}</h4>
                                <small class="text-muted">Sin Stock</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </a>
                        <form action="{{ route('hairdressing-suppliers.toggle-status', $hairdressingSupplier) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $hairdressingSupplier->is_active ? 'warning' : 'success' }} btn-sm w-100">
                                <i class="fas fa-{{ $hairdressingSupplier->is_active ? 'pause' : 'play' }}"></i>
                                {{ $hairdressingSupplier->is_active ? 'Desactivar' : 'Activar' }} Proveedor
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm" 
                                onclick="confirmDelete({{ $hairdressingSupplier->id }}, '{{ $hairdressingSupplier->name }}')">
                            <i class="fas fa-trash"></i> Eliminar Proveedor
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar el proveedor "<span id="supplierName"></span>"?
                <br><br>
                <strong>Esta acción no se puede deshacer.</strong>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function confirmDelete(supplierId, supplierName) {
    document.getElementById('supplierName').textContent = supplierName;
    document.getElementById('deleteForm').action = `/hairdressing-suppliers/${supplierId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush 