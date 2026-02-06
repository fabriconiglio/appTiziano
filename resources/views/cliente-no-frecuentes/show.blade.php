@extends('layouts.app')

@section('title', 'Detalles del Cliente No Frecuente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> Detalles del Cliente No Frecuente
                    </h5>
                    <div>
                        <a href="{{ route('cliente-no-frecuentes.edit', $clienteNoFrecuente) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('cliente-no-frecuentes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información del Cliente -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-user"></i> Información del Cliente
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del Cliente</label>
                            <div class="form-control-plaintext">
                                @if($clienteNoFrecuente->nombre)
                                    {{ $clienteNoFrecuente->nombre }}
                                @else
                                    <span class="text-muted">Sin nombre registrado</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <div class="form-control-plaintext">
                                @if($clienteNoFrecuente->telefono)
                                    <span class="text-primary">{{ $clienteNoFrecuente->telefono }}</span>
                                @else
                                    <span class="text-muted">Sin teléfono registrado</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información del Servicio -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-cut"></i> Información del Servicio
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha del Servicio</label>
                            <div class="form-control-plaintext">
                                <span class="fw-bold">{{ $clienteNoFrecuente->fecha->format('d/m/Y') }}</span>
                                <br>
                                <small class="text-muted">{{ $clienteNoFrecuente->fecha->format('H:i') }}</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Peluquero</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-info fs-6">{{ $clienteNoFrecuente->peluquero }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Valor del Servicio</label>
                            <div class="form-control-plaintext">
                                <span class="fw-bold text-success fs-5">${{ number_format($clienteNoFrecuente->monto, 2) }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Forma de Pago</label>
                            <div class="form-control-plaintext">
                                @php
                                    $badgeClass = match($clienteNoFrecuente->forma_pago) {
                                        'efectivo' => 'bg-success',
                                        'tarjeta' => 'bg-primary',
                                        'transferencia' => 'bg-info',
                                        'deudor' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-6">
                                    {{ \App\Models\ClienteNoFrecuente::FORMAS_PAGO[$clienteNoFrecuente->forma_pago] ?? 'Sin definir' }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Servicios Realizados</label>
                            <div class="form-control-plaintext">
                                @if($clienteNoFrecuente->servicios)
                                    {{ $clienteNoFrecuente->servicios }}
                                @else
                                    <span class="text-muted">Sin servicios especificados</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    @if($clienteNoFrecuente->observaciones)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-sticky-note"></i> Observaciones
                            </h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <p class="mb-0">{{ $clienteNoFrecuente->observaciones }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Información del Registro -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle"></i> Información del Registro
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Registrado por</label>
                            <div class="form-control-plaintext">
                                <span class="text-primary">{{ $clienteNoFrecuente->user->name }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de registro</label>
                            <div class="form-control-plaintext">
                                <span>{{ $clienteNoFrecuente->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        @if($clienteNoFrecuente->updated_at->ne($clienteNoFrecuente->created_at))
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Última actualización</label>
                            <div class="form-control-plaintext">
                                <span>{{ $clienteNoFrecuente->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Acciones -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Eliminar Cliente
                                    </button>
                                </div>
                                <div>
                                    <a href="{{ route('cliente-no-frecuentes.edit', $clienteNoFrecuente) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="{{ route('cliente-no-frecuentes.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-list"></i> Ver Lista
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" 
     aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
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
                                {{ $clienteNoFrecuente->nombre ?: 'Cliente sin nombre' }}
                            </h6>
                            <p class="card-text mb-1">
                                <strong>Fecha:</strong> {{ $clienteNoFrecuente->fecha->format('d/m/Y') }}
                            </p>
                            <p class="card-text mb-1">
                                <strong>Peluquero:</strong> {{ $clienteNoFrecuente->peluquero }}
                            </p>
                            <p class="card-text">
                                <strong>Monto:</strong> ${{ number_format($clienteNoFrecuente->monto, 2) }}
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
                <form action="{{ route('cliente-no-frecuentes.destroy', $clienteNoFrecuente) }}" method="POST" style="display: inline;">
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
@endsection
