@extends('layouts.app')

@section('title', 'Proveedores')

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Proveedores</h1>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
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
            <form action="{{ route('suppliers.index') }}" method="GET" class="row g-3">
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
                        <a href="{{ route('suppliers.index') }}" class="btn btn-light">
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
                                    <div class="fw-bold">{{ $supplier->products_count }}</div>
                                    <small class="text-muted">productos</small>
                                </td>
                                <td>
                                    <span class="badge {{ $supplier->status_badge_class }}">
                                        {{ $supplier->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('suppliers.show', $supplier) }}" 
                                           class="btn btn-info btn-sm"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" 
                                           class="btn btn-warning btn-sm"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('suppliers.toggle-status', $supplier) }}" 
                                              method="POST" 
                                              style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="btn btn-{{ $supplier->is_active ? 'secondary' : 'success' }} btn-sm"
                                                    title="{{ $supplier->is_active ? 'Desactivar' : 'Activar' }}">
                                                <i class="fas fa-{{ $supplier->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-danger btn-sm"
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3"></i>
                                        <h5>No hay proveedores registrados</h5>
                                        <p>Comienza agregando tu primer proveedor usando el botón "Nuevo Proveedor"</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Mostrando {{ $suppliers->firstItem() ?? 0 }} a {{ $suppliers->lastItem() ?? 0 }} de {{ $suppliers->total() }} resultados
                </div>
                <div>
                    {{ $suppliers->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Resumen</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Proveedores</h6>
                            <h3 class="mb-0">{{ $suppliers->total() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Proveedores Activos</h6>
                            <h3 class="mb-0">{{ $suppliers->where('is_active', true)->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6 class="card-title">Proveedores Inactivos</h6>
                            <h3 class="mb-0">{{ $suppliers->where('is_active', false)->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Con Productos</h6>
                            <h3 class="mb-0">{{ $suppliers->where('products_count', '>', 0)->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 