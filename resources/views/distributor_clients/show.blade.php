<!-- resources/views/clients/show.blade.php -->
@extends('layouts.app')

@section('title', 'Detalle de Cliente Distribuidor')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Información del Cliente Distribuidor: {{ $distributorClient->name }} {{ $distributorClient->surname }}</h5>
                <div>
                    <a href="{{ route('distributor-clients.quotations.create', $distributorClient) }}" class="btn btn-warning btn-sm me-2">
                        <i class="fas fa-file-invoice-dollar"></i> Nuevo Presupuesto
                    </a>
                    <a href="{{ route('distributor-clients.technical-records.create', $distributorClient) }}" class="btn btn-success btn-sm me-2">
                        <i class="fas fa-plus"></i> Nueva Ficha Técnica
                    </a>
                    <a href="{{ route('distributor-clients.edit', $distributorClient) }}" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('distributor-clients.index') }}" class="btn btn-secondary btn-sm">
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
                            <dd class="col-sm-8">{{ $distributorClient->name }} {{ $distributorClient->surname }}</dd>

                            <dt class="col-sm-4">Email:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->email ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">DNI:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->dni ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">Teléfono:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->phone ?? 'No registrado' }}</dd>

                            <dt class="col-sm-4">Fecha de Nacimiento:</dt>
                            <dd class="col-sm-8">
                                @if($distributorClient->birth_date && !is_string($distributorClient->birth_date))
                                    {{ $distributorClient->birth_date->format('d/m/Y') }}
                                @else
                                    {{ $distributorClient->birth_date ?? 'No registrada' }}
                                @endif
                            </dd>
                        </dl>
                    </div>

                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2 mb-3">Información Adicional</h6>
                        <dl class="row">
                            <dt class="col-sm-4">Observaciones:</dt>
                            <dd class="col-sm-8">{!! $distributorClient->observations ?? 'Sin observaciones' !!}</dd>
                            <dt class="col-sm-4">Domicilio:</dt>
                            <dd class="col-sm-8">{{ $distributorClient->domicilio ?? 'No registrado' }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Sección de Fichas Técnicas -->
                <div class="mt-4">
                    <h6 class="border-bottom pb-2 mb-3">Fichas Técnicas de Compra</h6>
                    
                    @if($distributorClient->distributorTechnicalRecords && $distributorClient->distributorTechnicalRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Monto</th>
                                        <th>Método de Pago</th>
                                        <th>Productos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($distributorClient->distributorTechnicalRecords as $record)
                                        <tr>
                                            <td>{{ $record->purchase_date->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @switch($record->purchase_type)
                                                    @case('al_por_mayor')
                                                        <span class="badge bg-primary">Al por Mayor</span>
                                                        @break
                                                    @case('al_por_menor')
                                                        <span class="badge bg-info">Al por Menor</span>
                                                        @break
                                                    @case('especial')
                                                        <span class="badge bg-warning">Especial</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">No especificado</span>
                                                @endswitch
                                            </td>
                                            <td>${{ number_format($record->final_amount, 2) }}</td>
                                            <td>{{ ucfirst($record->payment_method ?? 'No especificado') }}</td>
                                            <td>
                                                @if(!empty($record->products_purchased))
                                                    {{ count($record->products_purchased) }} producto(s)
                                                @else
                                                    0 productos
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('distributor-clients.technical-records.show', [$distributorClient, $record]) }}"
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('distributor-clients.technical-records.edit', [$distributorClient, $record]) }}"
                                                       class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ route('distributor-clients.technical-records.remito', [$distributorClient, $record]) }}"
                                                       class="btn btn-outline-danger btn-sm" target="_blank" style="border-color: #8B0000; color: #8B0000;">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $record->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No hay fichas técnicas registradas para este cliente distribuidor.
                            <a href="{{ route('distributor-clients.technical-records.create', $distributorClient) }}" class="alert-link">
                                Crear la primera ficha técnica
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modales de confirmación para eliminar fichas técnicas -->
    @foreach($distributorClient->distributorTechnicalRecords as $record)
        <div class="modal fade" id="deleteModal{{ $record->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $record->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel{{ $record->id }}">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            Confirmar Eliminación
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que quieres eliminar esta ficha técnica?</p>
                        <div class="alert alert-warning">
                            <strong>Fecha:</strong> {{ $record->purchase_date->format('d/m/Y H:i') }}<br>
                            <strong>Tipo:</strong> 
                            @switch($record->purchase_type)
                                @case('al_por_mayor')
                                    Al por Mayor
                                    @break
                                @case('al_por_menor')
                                    Al por Menor
                                    @break
                                @case('especial')
                                    Especial
                                    @break
                                @default
                                    No especificado
                            @endswitch<br>
                            <strong>Monto:</strong> ${{ number_format($record->final_amount, 2) }}<br>
                            <strong>Productos:</strong> {{ count($record->products_purchased ?? []) }} producto(s)
                        </div>
                        <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <form action="{{ route('distributor-clients.technical-records.destroy', [$distributorClient, $record]) }}" 
                              method="POST" 
                              style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

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
