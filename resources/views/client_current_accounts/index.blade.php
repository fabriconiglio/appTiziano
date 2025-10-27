@extends('layouts.app')

@section('title', 'Cuentas Corrientes - Peluquería')

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cuentas Corrientes - Peluquería</h1>
        <a href="{{ route('clients.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
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

    <!-- Buscador y Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('client-current-accounts.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Buscar por nombre, DNI, email o teléfono"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="debt_status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="with_debt" {{ request('debt_status') == 'with_debt' ? 'selected' : '' }}>
                            Con Deuda
                        </option>
                        <option value="up_to_date" {{ request('debt_status') == 'up_to_date' ? 'selected' : '' }}>
                            Al Día
                        </option>
                        <option value="in_favor" {{ request('debt_status') == 'in_favor' ? 'selected' : '' }}>
                            A Favor
                        </option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                    </button>
                    @if(request('search') || request('debt_status'))
                        <a href="{{ route('client-current-accounts.index') }}" class="btn btn-light">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Indicadores de filtros activos -->
    @if(request('search') || request('debt_status'))
        <div class="alert alert-info mb-4">
            <h6 class="alert-heading">
                <i class="fas fa-filter me-2"></i>
                Filtros aplicados:
            </h6>
            <div class="d-flex flex-wrap gap-2">
                @if(request('search'))
                    <span class="badge bg-primary">
                        <i class="fas fa-search me-1"></i>
                        Búsqueda: "{{ request('search') }}"
                    </span>
                @endif
                @if(request('debt_status'))
                    <span class="badge bg-secondary">
                        <i class="fas fa-chart-line me-1"></i>
                        Estado: 
                        @switch(request('debt_status'))
                            @case('with_debt')
                                Con Deuda
                                @break
                            @case('up_to_date')
                                Al Día
                                @break
                            @case('in_favor')
                                A Favor
                                @break
                        @endswitch
                    </span>
                @endif
            </div>
        </div>
    @endif

    <!-- Tabla de clientes -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Saldo Actual</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $client->full_name }}</div>
                                    <small class="text-muted">DNI: {{ $client->dni }}</small>
                                </td>
                                <td>{{ $client->email ?? 'No registrado' }}</td>
                                <td>{{ $client->phone ?? 'No registrado' }}</td>
                                <td>
                                    <span class="fw-bold {{ $client->current_balance > 0 ? 'text-danger' : ($client->current_balance < 0 ? 'text-success' : 'text-dark') }}">
                                        {{ $client->formatted_balance }}
                                    </span>
                                </td>
                                <td>
                                    @if($client->current_balance > 0)
                                        <span class="badge bg-danger">Con Deuda</span>
                                    @elseif($client->current_balance < 0)
                                        <span class="badge bg-success">A Favor</span>
                                    @else
                                        <span class="badge bg-secondary">Al Día</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('clients.current-accounts.show', $client) }}" 
                                           class="btn btn-info btn-sm"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('clients.current-accounts.create', $client) }}" 
                                           class="btn btn-success btn-sm"
                                           title="Nuevo movimiento">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm"
                                                title="Eliminar cuenta corriente"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $client->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    No hay clientes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
            Mostrando {{ $clients->firstItem() ?? 0 }} a {{ $clients->lastItem() ?? 0 }} de {{ $clients->total() }} resultados
        </div>
        <div>
            {{ $clients->appends(request()->query())->links() }}
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
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Deudas</h6>
                            <h3 class="mb-0">${{ number_format($clients->where('current_balance', '>', 0)->sum('current_balance'), 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Créditos</h6>
                            <h3 class="mb-0">${{ number_format(abs($clients->where('current_balance', '<', 0)->sum('current_balance')), 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6 class="card-title">Clientes con Deuda</h6>
                            <h3 class="mb-0">{{ $clients->where('current_balance', '>', 0)->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Clientes</h6>
                            <h3 class="mb-0">{{ $clients->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales de confirmación de eliminación -->
@foreach($clients as $client)
    <div class="modal fade" id="deleteModal{{ $client->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $client->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel{{ $client->id }}">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="fas fa-trash text-danger me-2"></i>
                            ¿Eliminar cuenta corriente?
                        </h6>
                        <p class="mb-2">¿Estás seguro de que quieres eliminar <strong>toda la cuenta corriente</strong> de:</p>
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">{{ $client->full_name }}</h6>
                                <p class="card-text mb-1">
                                    <strong>Saldo actual:</strong> 
                                    <span class="badge {{ $client->current_balance > 0 ? 'bg-danger' : ($client->current_balance < 0 ? 'bg-success' : 'bg-secondary') }}">
                                        ${{ number_format(abs($client->current_balance), 2) }}
                                        @if($client->current_balance > 0)
                                            (Debe)
                                        @elseif($client->current_balance < 0)
                                            (A favor)
                                        @else
                                            (Al día)
                                        @endif
                                    </span>
                                </p>
                                <p class="card-text">
                                    <strong>Movimientos:</strong> 
                                    <span class="badge bg-info">{{ $client->currentAccounts->count() }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>¡Atención!</strong> Esta acción eliminará <strong>TODOS</strong> los movimientos de la cuenta corriente y <strong>NO SE PUEDE DESHACER</strong>.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Cancelar
                    </button>
                    <form action="{{ route('clients.current-accounts.destroy-all', $client) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>
                            Eliminar Cuenta Corriente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection 