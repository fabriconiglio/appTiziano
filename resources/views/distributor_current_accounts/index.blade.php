@extends('layouts.app')

@section('title', 'Cuentas Corrientes - Distribuidores')

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cuentas Corrientes - Distribuidores</h1>
        <a href="{{ route('distributor-clients.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Clientes Distribuidores
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
            <form action="{{ route('distributor-current-accounts.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control"
                           placeholder="Buscar por nombre, DNI, email o teléfono"
                           value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('distributor-current-accounts.index') }}" class="btn btn-light">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de distribuidores -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Distribuidor</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Saldo Actual</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($distributorClients as $client)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $client->full_name }}</div>
                                    <small class="text-muted">DNI: {{ $client->dni }}</small>
                                </td>
                                <td>{{ $client->email ?? 'No registrado' }}</td>
                                <td>{{ $client->phone ?? 'No registrado' }}</td>
                                <td>
                                    <span class="fw-bold {{ $client->current_balance > 0 ? 'text-danger' : ($client->current_balance < 0 ? 'text-success' : 'text-dark') }}">
                                        ${{ $client->formatted_balance }}
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
                                        <a href="{{ route('distributor-clients.current-accounts.show', $client) }}" 
                                           class="btn btn-info btn-sm"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i> Ver Detalle
                                        </a>
                                        <a href="{{ route('distributor-clients.current-accounts.create', $client) }}" 
                                           class="btn btn-success btn-sm"
                                           title="Nuevo movimiento">
                                            <i class="fas fa-plus"></i> Nuevo Movimiento
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm"
                                                title="Eliminar cuenta corriente"
                                                onclick="confirmDelete('{{ $client->id }}', '{{ $client->full_name }}')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    No hay distribuidores registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Deudas</h6>
                            <h3 class="mb-0">${{ number_format($distributorClients->sum('current_balance'), 2, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6 class="card-title">Distribuidores con Deuda</h6>
                            <h3 class="mb-0">{{ $distributorClients->where('current_balance', '>', 0)->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Distribuidores</h6>
                            <h3 class="mb-0">{{ $distributorClients->count() }}</h3>
                        </div>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar la cuenta corriente de <strong id="clientName"></strong>?</p>
                <p class="text-danger"><small>Esta acción eliminará todos los movimientos de la cuenta corriente y no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Cuenta Corriente</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(clientId, clientName) {
    document.getElementById('clientName').textContent = clientName;
    document.getElementById('deleteForm').action = `{{ route('distributor-clients.current-accounts.destroy-all', ':id') }}`.replace(':id', clientId);
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endsection 