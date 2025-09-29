<!-- resources/views/clients/index.blade.php -->
@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <div class="container">
        <!-- Cabecera -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Clientes</h1>
            <a href="{{ route('clients.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </a>
        </div>

        <!-- Mensajes de éxito o error -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Buscador -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('clients.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                               placeholder="Buscar por nombre o DNI"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary">Buscar</button>
                        @if(request('search'))
                            <a href="{{ route('clients.index') }}" class="btn btn-light">Limpiar</a>
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
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>DNI</th>
                            <th>Última Visita</th>
                            <th>Estado de Cuenta</th>
                            <th width="200">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td><strong>{{ $client->name }} {{ $client->surname }}</strong></td>
                                <td>{{ $client->phone ?? 'No registrado' }}</td>
                                <td>{{ $client->email ?? 'No registrado' }}</td>
                                <td>{{ $client->dni ?? 'No registrado' }}</td>
                                <td>
                                    @if($client->technicalRecords->count() > 0)
                                        <span class="badge bg-success">{{ $client->technicalRecords->max('service_date')?->format('d/m/Y') }}</span>
                                    @else
                                        <span class="badge bg-secondary">Sin visitas</span>
                                    @endif
                                </td>
                                <td>
                                    @if($client->has_debt)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Con Deuda
                                        </span>
                                        <br>
                                        <small class="text-muted">${{ number_format($client->current_balance, 2) }}</small>
                                    @elseif($client->current_balance > 0)
                                        <span class="badge bg-warning">
                                            <i class="fas fa-info-circle"></i> Con Crédito
                                        </span>
                                        <br>
                                        <small class="text-muted">${{ number_format($client->current_balance, 2) }}</small>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Al Día
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('clients.show', $client) }}"
                                           class="btn btn-info btn-sm"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('clients.edit', $client) }}"
                                           class="btn btn-warning btn-sm"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('clients.technical-records.create', $client) }}"
                                           class="btn btn-success btn-sm"
                                           title="Nueva ficha técnica">
                                            <i class="fas fa-file-medical"></i>
                                        </a>
                                        <a href="{{ route('clients.current-accounts.show', $client) }}"
                                           class="btn btn-info btn-sm"
                                           title="Cuenta Corriente">
                                            <i class="fas fa-calculator"></i>
                                        </a>
                                        <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este cliente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-users fa-3x mb-3"></i>
                                        <p>No se encontraron clientes</p>
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
                        Mostrando {{ $clients->firstItem() ?? 0 }} a {{ $clients->lastItem() ?? 0 }} de {{ $clients->total() }} resultados
                    </div>
                    <div>
                        {{ $clients->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes Desactivados -->
        @php
            $trashedClients = \App\Models\Client::onlyTrashed()->get();
        @endphp
        @if($trashedClients->count() > 0)
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-archive"></i> <strong>Clientes Desactivados ({{ $trashedClients->count() }})</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-warning">
                            <tr>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>DNI</th>
                                <th>Fecha de Desactivación</th>
                                <th width="150">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($trashedClients as $client)
                                <tr>
                                    <td><strong>{{ $client->name }} {{ $client->surname }}</strong></td>
                                    <td>{{ $client->phone ?? 'No registrado' }}</td>
                                    <td>{{ $client->email ?? 'No registrado' }}</td>
                                    <td>{{ $client->dni ?? 'No registrado' }}</td>
                                    <td>{{ $client->deleted_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('clients.restore', $client->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" title="Reactivar">
                                                <i class="fas fa-undo"></i> Reactivar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            // Auto-cerrar alertas después de 5 segundos
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    var alerts = document.querySelectorAll('.alert');
                    alerts.forEach(function(alert) {
                        var bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    });
                }, 5000);
            });
        </script>
    @endpush
@endsection
