@extends('layouts.app')

@section('title', 'Descuentos de Distribuidores')

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-percent"></i> Descuentos de Distribuidores</h1>
        <a href="{{ route('distributor-discounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Descuento
        </a>
    </div>

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

    <!-- Buscador -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('distributor-discounts.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Buscar por descripción, producto, SKU o distribuidor..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('distributor-discounts.index') }}" class="btn btn-light">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de descuentos -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Distribuidor</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Producto</th>
                            <th>Validez</th>
                            <th>Estado</th>
                            <th>Usos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($discounts as $discount)
                            <tr>
                                <td>
                                    @if($discount->applies_to_all_distributors)
                                        <div class="fw-bold">
                                            <span class="badge bg-primary">Todos los distribuidores</span>
                                        </div>
                                        <small class="text-muted">Aplicable a todos los distribuidores</small>
                                    @else
                                        <div class="fw-bold">{{ $discount->distributorClient->full_name }}</div>
                                        <small class="text-muted">{{ $discount->distributorClient->email ?? 'Sin email' }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $discount->description }}</div>
                                    @if($discount->conditions)
                                        <small class="text-muted">{{ Str::limit($discount->conditions, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $discount->discount_type_text }}</span>
                                </td>
                                <td>
                                    @if($discount->applies_to_all_products)
                                        <span class="badge bg-success">Todos los productos</span>
                                    @elseif($discount->supplierInventory)
                                        <div class="fw-bold">{{ $discount->supplierInventory->product_name }}</div>
                                        <small class="text-muted">SKU: {{ $discount->supplierInventory->sku }}</small>
                                    @elseif($discount->product_name || $discount->product_sku)
                                        <div class="fw-bold">{{ $discount->product_name ?? 'Sin nombre' }}</div>
                                        @if($discount->product_sku)
                                            <small class="text-muted">SKU: {{ $discount->product_sku }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($discount->valid_from || $discount->valid_until)
                                        <div>
                                            @if($discount->valid_from)
                                                <small>Desde: {{ $discount->valid_from->format('d/m/Y') }}</small><br>
                                            @endif
                                            @if($discount->valid_until)
                                                <small>Hasta: {{ $discount->valid_until->format('d/m/Y') }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Sin límite</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $discount->status_badge_class }}">
                                        {{ $discount->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($discount->max_uses)
                                        {{ $discount->current_uses }}/{{ $discount->max_uses }}
                                        @php
                                            $percentage = $discount->max_uses > 0 ? ($discount->current_uses / $discount->max_uses) * 100 : 0;
                                            $progressWidth = min($percentage, 100);
                                        @endphp
                                        <div class="progress mt-1" style="height: 5px;">
                                            <div class="progress-bar" data-width="{{ $progressWidth }}"></div>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ $discount->current_uses }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group" aria-label="Acciones">
                                        <a href="{{ route('distributor-discounts.show', $discount) }}" 
                                           class="btn btn-info btn-sm" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('distributor-discounts.edit', $discount) }}" 
                                           class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('distributor-discounts.toggle-status', $discount) }}" 
                                              method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn {{ $discount->is_active ? 'btn-secondary' : 'btn-success' }} btn-sm"
                                                    title="{{ $discount->is_active ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas {{ $discount->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $discount->id }}"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-percent fa-4x mb-3"></i>
                                        <h5>No hay descuentos registrados</h5>
                                        <p class="mb-0">Comienza agregando tu primer descuento usando el botón "Nuevo Descuento"</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    @if($discounts->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Mostrando {{ $discounts->firstItem() ?? 0 }} a {{ $discounts->lastItem() ?? 0 }} 
                de {{ $discounts->total() }} descuentos
            </div>
            <div>
                {{ $discounts->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Modales de confirmación de eliminación -->
@foreach($discounts as $discount)
    <div class="modal fade" id="deleteModal{{ $discount->id }}" tabindex="-1" 
         aria-labelledby="deleteModalLabel{{ $discount->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel{{ $discount->id }}">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <p class="mb-2">¿Estás seguro de que quieres eliminar este descuento?</p>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">{{ $discount->description }}</h6>
                                <p class="card-text mb-1">
                                    <strong>Distribuidor:</strong> 
                                    @if($discount->applies_to_all_distributors)
                                        <span class="badge bg-primary">Todos los distribuidores</span>
                                    @else
                                        {{ $discount->distributorClient->full_name }}
                                    @endif
                                </p>
                                <p class="card-text mb-1">
                                    <strong>Tipo:</strong> {{ $discount->discount_type_text }}
                                </p>
                                <p class="card-text">
                                    <strong>Estado:</strong> 
                                    <span class="badge {{ $discount->status_badge_class }}">
                                        {{ $discount->status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>¡Atención!</strong> Esta acción <strong>NO SE PUEDE DESHACER</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancelar
                    </button>
                    <form action="{{ route('distributor-discounts.destroy', $discount) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            Eliminar Descuento
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar ancho dinámico a las barras de progreso
    document.querySelectorAll('.progress-bar[data-width]').forEach(function(bar) {
        const width = bar.getAttribute('data-width');
        bar.style.width = width + '%';
    });
});
</script>
@endsection