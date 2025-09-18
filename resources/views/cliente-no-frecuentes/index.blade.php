@extends('layouts.app')

@section('title', 'Clientes No Frecuentes')

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-user-clock"></i> Clientes No Frecuentes</h1>
        <a href="{{ route('cliente-no-frecuentes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Cliente
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
            <form action="{{ route('cliente-no-frecuentes.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Buscar por nombre, peluquero, servicios o teléfono..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('cliente-no-frecuentes.index') }}" class="btn btn-light">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de clientes -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Peluquero</th>
                            <th>Servicios</th>
                            <th>Valor</th>
                            <th>Registrado por</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientes as $cliente)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $cliente->fecha->format('d/m/Y') }}</span>
                                    <br>
                                    <small class="text-muted">{{ $cliente->fecha->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($cliente->nombre)
                                        <div class="fw-bold">{{ $cliente->nombre }}</div>
                                    @else
                                        <span class="text-muted">Sin nombre</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cliente->telefono)
                                        <span class="text-primary">{{ $cliente->telefono }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $cliente->peluquero }}</span>
                                </td>
                                <td>
                                    @if($cliente->servicios)
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="{{ $cliente->servicios }}">
                                            {{ Str::limit($cliente->servicios, 50) }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-success">${{ number_format($cliente->monto, 2) }}</span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $cliente->user->name }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group" aria-label="Acciones">
                                        <a href="{{ route('cliente-no-frecuentes.show', $cliente) }}" 
                                           class="btn btn-info btn-sm" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('cliente-no-frecuentes.edit', $cliente) }}" 
                                           class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $cliente->id }}"
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
                                        <i class="fas fa-user-clock fa-4x mb-3"></i>
                                        <h5>No hay clientes no frecuentes registrados</h5>
                                        <p class="mb-0">Comienza agregando tu primer cliente usando el botón "Nuevo Cliente"</p>
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
    @if($clientes->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Mostrando {{ $clientes->firstItem() ?? 0 }} a {{ $clientes->lastItem() ?? 0 }} 
                de {{ $clientes->total() }} clientes
            </div>
            <div>
                {{ $clientes->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Modales de confirmación de eliminación -->
@foreach($clientes as $cliente)
    <div class="modal fade" id="deleteModal{{ $cliente->id }}" tabindex="-1" 
         aria-labelledby="deleteModalLabel{{ $cliente->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel{{ $cliente->id }}">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <p class="mb-2">¿Estás seguro de que quieres eliminar este cliente no frecuente?</p>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    {{ $cliente->nombre ?: 'Cliente sin nombre' }}
                                </h6>
                                <p class="card-text mb-1">
                                    <strong>Fecha:</strong> {{ $cliente->fecha->format('d/m/Y') }}
                                </p>
                                <p class="card-text mb-1">
                                    <strong>Peluquero:</strong> {{ $cliente->peluquero }}
                                </p>
                                <p class="card-text">
                                    <strong>Monto:</strong> ${{ number_format($cliente->monto, 2) }}
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
                    <form action="{{ route('cliente-no-frecuentes.destroy', $cliente) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            Eliminar Cliente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
