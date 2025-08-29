<!-- resources/views/clients/index.blade.php -->
@extends('layouts.app')

@section('title', 'Clientes Distribuidores')

@section('content')
    <div class="container">
        <!-- Cabecera -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Clientes Distribuidores</h1>
            <div>
                <a href="{{ route('distributor-current-accounts.index') }}" class="btn btn-info me-2">
                    <i class="fas fa-calculator"></i> Cuentas Corrientes
                </a>
                <a href="{{ route('distributor-clients.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Cliente Distribuidor
                </a>
            </div>
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
                <form action="{{ route('distributor-clients.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                               placeholder="Buscar por nombre o DNI"
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary">Buscar</button>
                        @if(request('search'))
                            <a href="{{ route('distributor-clients.index') }}" class="btn btn-light">Limpiar</a>
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
                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>DNI</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($distributorClients as $distributorClient)
                            <tr>
                                <td>{{ $distributorClient->name }} {{ $distributorClient->surname }}</td>
                                <td>{{ $distributorClient->phone ?? 'No registrado' }}</td>
                                <td>{{ $distributorClient->email ?? 'No registrado' }}</td>
                                <td>{{ $distributorClient->dni ?? 'No registrado' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('distributor-clients.show', $distributorClient) }}"
                                           class="btn btn-info btn-sm"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('distributor-clients.edit', $distributorClient) }}"
                                           class="btn btn-warning btn-sm"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('distributor-clients.technical-records.create', $distributorClient) }}"
                                           class="btn btn-success btn-sm"
                                           title="Nueva ficha técnica">
                                            <i class="fas fa-file-medical"></i>
                                        </a>
                                        <a href="{{ route('distributor-clients.current-accounts.show', $distributorClient) }}"
                                           class="btn btn-info btn-sm"
                                           title="Cuenta Corriente">
                                            <i class="fas fa-calculator"></i>
                                        </a>
                                        <form action="{{ route('distributor-clients.destroy', $distributorClient) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de eliminar este cliente distribuidor?');">
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
                                <td colspan="6" class="text-center py-4">
                                    No se encontraron clientes distribuidores
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Mostrando {{ $distributorClients->firstItem() ?? 0 }} a {{ $distributorClients->lastItem() ?? 0 }} de {{ $distributorClients->total() }} resultados
                    </div>
                    <div>
                        {{ $distributorClients->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        @php
            $trashedClients = \App\Models\DistributorClient::onlyTrashed()->get();
        @endphp
        @if($trashedClients->count())
            <div class="card mt-4">
                <div class="card-header bg-warning text-dark">
                    <strong>Clientes Distribuidores Desactivados</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>DNI</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($trashedClients as $client)
                                <tr>
                                    <td>{{ $client->name }} {{ $client->surname }}</td>
                                    <td>{{ $client->phone ?? 'No registrado' }}</td>
                                    <td>{{ $client->email ?? 'No registrado' }}</td>
                                    <td>{{ $client->dni ?? 'No registrado' }}</td>
                                    <td>
                                        <form action="{{ route('distributor-clients.restore', $client->id) }}" method="POST" style="display:inline-block;">
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

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro que desea eliminar este cliente? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function deleteDistributorClient(id) {
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                const form = document.getElementById('deleteForm');
                form.action = `/distributor-clients/${id}`;
                modal.show();
            }

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
