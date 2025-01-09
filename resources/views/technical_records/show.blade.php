<!-- resources/views/technical_records/show.blade.php -->
@extends('layouts.app')

@section('title', 'Detalle de Ficha Técnica')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ficha Técnica - {{ $client->name }} {{ $client->surname }}</h5>
                        <div>
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-secondary btn-sm me-2">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <a href="{{ route('clients.technical-records.edit', [$client, $technicalRecord]) }}"
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar
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
                            <!-- Información básica -->
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Información del Servicio</h6>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4">Fecha:</dt>
                                            <dd class="col-sm-8">{{ $technicalRecord->service_date->format('d/m/Y H:i') }}</dd>

                                            <dt class="col-sm-4">Estilista:</dt>
                                            <dd class="col-sm-8">{{ $technicalRecord->stylist->name }}</dd>

                                            <dt class="col-sm-4">Tipo de Cabello:</dt>
                                            <dd class="col-sm-8">{{ $technicalRecord->hair_type ?? 'No especificado' }}</dd>

                                            <dt class="col-sm-4">Cuero Cabelludo:</dt>
                                            <dd class="col-sm-8">{{ $technicalRecord->scalp_condition ?? 'No especificado' }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del color -->
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Información del Color</h6>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4">Color Actual:</dt>
                                            <dd class="col-sm-8">{{ $technicalRecord->current_hair_color ?? 'No especificado' }}</dd>

                                            <dt class="col-sm-4">Color Deseado:</dt>
                                            <dd class="col-sm-8">{{ $technicalRecord->desired_hair_color ?? 'No especificado' }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tratamientos y Productos -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Tratamientos y Productos</h6>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-bold">Tratamientos Realizados:</h6>
                                <p class="mb-4">{{ $technicalRecord->hair_treatments ?? 'No se registraron tratamientos' }}</p>

                                <h6 class="fw-bold">Productos Utilizados:</h6>
                                @if($products && $products->count() > 0)
                                    <ul class="list-group list-group-flush">
                                        @foreach($products as $product)
                                            <li class="list-group-item">{{ $product->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No se registraron productos</p>
                                @endif
                            </div>
                        </div>

                        <!-- Fotos del tratamiento -->
                        @if($technicalRecord->photos && count($technicalRecord->photos) > 0)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Fotos del Tratamiento</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($technicalRecord->photos as $photo)
                                            <div class="col-md-4 mb-3">
                                                <a href="{{ Storage::url($photo) }}" target="_blank">
                                                    <img src="{{ Storage::url($photo) }}"
                                                         class="img-fluid rounded"
                                                         alt="Foto del tratamiento">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Observaciones y Notas -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Observaciones y Notas</h6>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-bold">Observaciones:</h6>
                                <p class="mb-4">{{ $technicalRecord->observations ?? 'Sin observaciones' }}</p>

                                <h6 class="fw-bold">Notas para Próxima Cita:</h6>
                                <p class="mb-0">{{ $technicalRecord->next_appointment_notes ?? 'Sin notas para próxima cita' }}</p>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash"></i> Eliminar Ficha
                            </button>
                            <a href="{{ route('clients.technical-records.edit', [$client, $technicalRecord]) }}"
                               class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar Ficha
                            </a>
                        </div>
                    </div>
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
                    ¿Está seguro que desea eliminar esta ficha técnica? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('clients.technical-records.destroy', [$client, $technicalRecord]) }}" method="POST">
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
