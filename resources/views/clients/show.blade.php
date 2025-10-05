<!-- resources/views/clients/show.blade.php -->
@extends('layouts.app')

@section('title', 'Detalle de Cliente')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Información del Cliente</h5>
                <div>
                    <a href="{{ route('clients.technical-records.create', $client) }}" class="btn btn-success btn-sm me-2">
                        <i class="fas fa-file-medical"></i> Nueva Ficha Técnica
                    </a>
                    <a href="{{ route('clients.current-accounts.show', $client) }}" class="btn btn-info btn-sm me-2">
                        <i class="fas fa-calculator"></i> Cuenta Corriente
                    </a>
                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-sm">
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

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Datos Personales</h6>
                        <dl class="row">
                            <dt class="col-sm-4">Nombre Completo:</dt>
                            <dd class="col-sm-8">{{ $client->name }} {{ $client->surname }}</dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">{{ $client->email ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">DNI:</dt>
                            <dd class="col-sm-8">{{ $client->dni ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8">{{ $client->phone ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">Fecha de Nacimiento:</dt>
                            <dd class="col-sm-8">
                                @if($client->birth_date && !is_string($client->birth_date))
                                    {{ $client->birth_date->format('d/m/Y') }}
                                @else
                                    {{ $client->birth_date ?? 'No registrada' }}
                                @endif
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Información Adicional</h6>
                        <dl class="row">
                            <dt class="col-sm-4">Domicilio:</dt>
                            <dd class="col-sm-8">{{ $client->domicilio ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">Observaciones:</dt>
                            <dd class="col-sm-8">{!! $client->observations ?? 'Sin observaciones' !!}</dd>

                            <!-- MOD-030 (main): Información de cuenta corriente -->
                            <dt class="col-sm-4">Cuenta Corriente:</dt>
                            <dd class="col-sm-8">
                                @php $balance = $client->getCurrentBalance(); @endphp
                                @if($balance > 0)
                                    <span class="badge bg-danger">Debe: ${{ number_format($balance, 2) }}</span>
                                @elseif($balance < 0)
                                    <span class="badge bg-success">A favor: ${{ number_format(abs($balance), 2) }}</span>
                                @else
                                    <span class="badge bg-secondary">Al día</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Historial de Fichas Técnicas -->
                <div class="mt-4">
                    <h5 class="border-bottom pb-2 mb-3">Historial de Fichas Técnicas</h5>

                    @if($technicalRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Estilista</th>
                                    <th>Tratamientos</th>
                                    <th>Próxima Cita</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($technicalRecords as $record)
                                    <tr>
                                        <td>{{ $record->service_date->format('d/m/Y H:i') }}</td>
                                        <td>{{ $record->stylist->name }}</td>
                                        <td>{{ Str::limit($record->hair_treatments, 50) }}</td>
                                        <td>{{ $record->next_appointment_notes ? Str::limit($record->next_appointment_notes, 30) : 'No especificada' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('clients.technical-records.show', [$client, $record]) }}"
                                                   class="btn btn-info btn-sm" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('clients.technical-records.edit', [$client, $record]) }}"
                                                   class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Eliminar"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal{{ $record->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal de Confirmación de Eliminación -->
                                    <div class="modal fade" id="deleteModal{{ $record->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $record->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $record->id }}">
                                                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                                        Confirmar Eliminación
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-3">
                                                        ¿Estás seguro de que quieres <strong>eliminar permanentemente</strong> esta ficha técnica?
                                                    </p>
                                                    <div class="alert alert-warning">
                                                        <strong>Fecha:</strong> {{ $record->service_date->format('d/m/Y H:i') }}<br>
                                                        <strong>Estilista:</strong> {{ $record->stylist->name }}<br>
                                                        <strong>Tratamientos:</strong> {{ Str::limit($record->hair_treatments, 80) }}
                                                    </div>
                                                    <p class="text-danger mb-0">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        <strong>Esta acción no se puede deshacer.</strong>
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="fas fa-times me-1"></i> Cancelar
                                                    </button>
                                                    <form action="{{ route('clients.technical-records.destroy', [$client, $record]) }}" 
                                                          method="POST" 
                                                          style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash me-1"></i> Sí, Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $technicalRecords->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            No hay fichas técnicas registradas para este cliente.
                            <a href="{{ route('clients.technical-records.create', $client) }}" class="alert-link">
                                Crear nueva ficha técnica
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
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
