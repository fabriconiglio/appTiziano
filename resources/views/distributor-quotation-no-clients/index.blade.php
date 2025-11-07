@extends('layouts.app')

@section('title', 'Presupuestos Clientes No Registrados - Distribuidora')

@section('content')
<div class="container-fluid">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Presupuestos Clientes No Registrados</h1>
        <div>
            <a href="{{ route('distributor-quotation-no-clients.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo Presupuesto
            </a>
        </div>
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
    <x-filters 
        :route="route('distributor-quotation-no-clients.index')" 
        :filters="[]" 
        :showSearch="true"
        searchPlaceholder="Buscar por número de presupuesto, nombre, teléfono o email" />

    <!-- Tabla de presupuestos -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Fecha</th>
                            <th>Válido Hasta</th>
                            <th>Tipo</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $quotation)
                            <tr>
                                <td>
                                    <strong>{{ $quotation->quotation_number }}</strong>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $quotation->nombre ?? 'Sin nombre' }}</div>
                                    @if($quotation->direccion)
                                        <small class="text-muted">{{ $quotation->direccion }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($quotation->telefono)
                                        <div><i class="fas fa-phone"></i> {{ $quotation->telefono }}</div>
                                    @endif
                                    @if($quotation->email)
                                        <div><i class="fas fa-envelope"></i> {{ $quotation->email }}</div>
                                    @endif
                                    @if(!$quotation->telefono && !$quotation->email)
                                        <span class="text-muted">Sin contacto</span>
                                    @endif
                                </td>
                                <td>{{ $quotation->quotation_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="{{ $quotation->valid_until->isPast() ? 'text-danger' : 'text-success' }}">
                                        {{ $quotation->valid_until->format('d/m/Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $quotation->type_formatted }}</span>
                                </td>
                                <td>
                                    <strong>${{ number_format($quotation->final_amount, 2, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @if($quotation->status === 'active')
                                        @if($quotation->isExpired())
                                            <span class="badge bg-warning">Vencido</span>
                                        @else
                                            <span class="badge bg-success">Activo</span>
                                        @endif
                                    @elseif($quotation->status === 'cancelled')
                                        <span class="badge bg-secondary">Cancelado</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $quotation->status_formatted }}</span>
                                    @endif
                                </td>
                                <td>{{ $quotation->user->name }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('distributor-quotation-no-clients.show', $quotation) }}" 
                                           class="btn btn-info btn-sm"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($quotation->isActive())
                                            <a href="{{ route('distributor-quotation-no-clients.edit', $quotation) }}" 
                                               class="btn btn-warning btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('distributor-quotation-no-clients.export-pdf', $quotation) }}" 
                                           class="btn btn-danger btn-sm"
                                           title="Exportar PDF"
                                           target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        @if($quotation->isActive())
                                            <button type="button" class="btn btn-warning btn-sm"
                                                    title="Cancelar"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#cancelQuotationModal{{ $quotation->id }}">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-danger btn-sm mx-1"
                                                title="Eliminar"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteQuotationModal{{ $quotation->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No hay presupuestos registrados.</p>
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
            Mostrando {{ $quotations->firstItem() ?? 0 }} a {{ $quotations->lastItem() ?? 0 }} de {{ $quotations->total() }} resultados
        </div>
        <div>
            {{ $quotations->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Modales de Confirmación -->
@foreach($quotations as $quotation)
    <!-- Modal de Cancelación -->
    <div class="modal fade" id="cancelQuotationModal{{ $quotation->id }}" tabindex="-1" aria-labelledby="cancelQuotationModalLabel{{ $quotation->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelQuotationModalLabel{{ $quotation->id }}">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmar Cancelación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        ¿Estás seguro de que quieres <strong>cancelar</strong> este presupuesto?
                    </p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Presupuesto:</strong> {{ $quotation->quotation_number }}<br>
                        <strong>Cliente:</strong> {{ $quotation->nombre ?? 'Sin nombre' }}<br>
                        <strong>Monto:</strong> ${{ number_format($quotation->final_amount, 2) }}
                    </div>
                    <p class="text-muted small">
                        Esta acción cambiará el estado del presupuesto a "Cancelado" y no se podrá revertir.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>No, mantener activo
                    </button>
                    <form action="{{ route('distributor-quotation-no-clients.change-status', $quotation) }}" 
                          method="POST" style="display: inline;">
                        @csrf
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-ban me-2"></i>Sí, cancelar presupuesto
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Eliminación -->
    <div class="modal fade" id="deleteQuotationModal{{ $quotation->id }}" tabindex="-1" aria-labelledby="deleteQuotationModalLabel{{ $quotation->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteQuotationModalLabel{{ $quotation->id }}">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        ¿Estás seguro de que quieres <strong>eliminar permanentemente</strong> este presupuesto?
                    </p>
                    <div class="alert alert-danger">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Presupuesto:</strong> {{ $quotation->quotation_number }}<br>
                        <strong>Cliente:</strong> {{ $quotation->nombre ?? 'Sin nombre' }}<br>
                        <strong>Monto:</strong> ${{ number_format($quotation->final_amount, 2) }}
                    </div>
                    <p class="text-muted small">
                        <strong>⚠️ ADVERTENCIA:</strong> Esta acción es irreversible. Se eliminarán todos los datos del presupuesto incluyendo productos, fotos y observaciones.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>No, mantener presupuesto
                    </button>
                    <form action="{{ route('distributor-quotation-no-clients.destroy', $quotation) }}" 
                          method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Sí, eliminar permanentemente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection

