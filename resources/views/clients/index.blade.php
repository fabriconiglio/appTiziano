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
                               placeholder="Buscar por nombre o teléfono..."
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
                        <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Última Visita</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>{{ $client->name }} {{ $client->surname }}</td>
                                <td>{{ $client->phone ?? 'No registrado' }}</td>
                                <td>{{ $client->email ?? 'No registrado' }}</td>
                                <td>
                                    {{ $client->technicalRecords->max('service_date')?->format('d/m/Y') ?? 'Sin visitas' }}
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
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
                                        <button type="button"
                                                class="btn btn-danger btn-sm"
                                                onclick="deleteClient({{ $client->id }})"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    No se encontraron clientes
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
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
            function deleteClient(clientId) {
                const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                const form = document.getElementById('deleteForm');
                form.action = `/clients/${clientId}`;
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
