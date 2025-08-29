@extends('layouts.app')

@section('title', 'Proveedores de Peluquería')

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Proveedores de Peluquería</h1>
        <a href="{{ route('hairdressing-suppliers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Proveedor
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
            <form action="{{ route('hairdressing-suppliers.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Buscar por nombre, contacto, email, teléfono o CUIT"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('hairdressing-suppliers.index') }}" class="btn btn-light">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de proveedores -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Contacto</th>
                            <th>Información de Contacto</th>
                            <th>Productos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $supplier->name }}</div>
                                    @if($supplier->business_name)
                                        <small class="text-muted">{{ $supplier->business_name }}</small>
                                    @endif
                                    @if($supplier->cuit)
                                        <br><small class="text-muted">CUIT: {{ $supplier->cuit }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->contact_person)
                                        <div class="fw-bold">{{ $supplier->contact_person }}</div>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->email)
                                        <div><i class="fas fa-envelope text-muted"></i> {{ $supplier->email }}</div>
                                    @endif
                                    @if($supplier->phone)
                                        <div><i class="fas fa-phone text-muted"></i> {{ $supplier->phone }}</div>
                                    @endif
                                    @if($supplier->website)
                                        <div><i class="fas fa-globe text-muted"></i> <a href="{{ $supplier->website }}" target="_blank">{{ $supplier->website }}</a></div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $supplier->products_count }} productos</span>
                                    @if($supplier->total_inventory_value > 0)
                                        <br><small class="text-muted">${{ number_format($supplier->total_inventory_value, 2) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $supplier->status_badge_class }}">
                                        {{ $supplier->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('hairdressing-suppliers.show', $supplier) }}" 
                                           class="btn btn-sm btn-info" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('hairdressing-suppliers.edit', $supplier) }}" 
                                           class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('hairdressing-suppliers.toggle-status', $supplier) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-{{ $supplier->is_active ? 'secondary' : 'success' }}" 
                                                    title="{{ $supplier->is_active ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas fa-{{ $supplier->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete({{ $supplier->id }}, '{{ $supplier->name }}')" 
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-building fa-3x mb-3"></i>
                                        <h5>No hay proveedores registrados</h5>
                                        <p>Comienza agregando tu primer proveedor de peluquería</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($suppliers->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $suppliers->links() }}
                </div>
            @endif
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