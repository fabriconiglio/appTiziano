@extends('layouts.app')

@section('title', 'Facturación AFIP')

@section('content')
<div class="container-fluid">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-invoice"></i> Facturación AFIP</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('facturacion.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Factura
            </a>
            <a href="{{ route('facturacion.configuration') }}" class="btn btn-secondary">
                <i class="fas fa-cog"></i> Configuración
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <!-- Buscador -->
                    <x-filters 
                        :route="route('facturacion.index')" 
                        :filters="[]" 
                        :showSearch="true"
                        searchPlaceholder="Buscar por número de factura, cliente, CUIT..." />

                    <!-- Tabla de facturas -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>CAE</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong>{{ $invoice->formatted_number }}</strong>
                                    </td>
                                    <td>{{ $invoice->client_full_name }}</td>
                                    <td>
                                        <span class="badge bg-info text-dark">Factura {{ $invoice->invoice_type }}</span>
                                    </td>
                                    <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                    <td>${{ number_format($invoice->total, 2, ',', '.') }}</td>
                                    <td>
                                        @switch($invoice->status)
                                            @case('draft')
                                                <span class="badge bg-secondary">Borrador</span>
                                                @break
                                            @case('sent')
                                                <span class="badge bg-warning text-dark">Enviada</span>
                                                @break
                                            @case('authorized')
                                                <span class="badge bg-success">Autorizada</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rechazada</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-dark">Cancelada</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($invoice->cae)
                                            <small class="text-muted">{{ $invoice->cae }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('facturacion.show', $invoice->id) }}" 
                                               class="btn btn-info btn-sm" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($invoice->status === 'draft')
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        title="Enviar a AFIP"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#sendToAfipModal{{ $invoice->id }}">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            @endif
                                            
                                            @if($invoice->canBeCancelled())
                                                <form action="{{ route('facturacion.cancel', $invoice->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm" 
                                                            title="Cancelar" 
                                                            onclick="return confirm('¿Cancelar factura?')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        No hay facturas registradas
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-center">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales de confirmación para enviar a AFIP -->
@foreach($invoices as $invoice)
    @if($invoice->status === 'draft')
    <div class="modal fade" id="sendToAfipModal{{ $invoice->id }}" tabindex="-1" role="dialog" aria-labelledby="sendToAfipModalLabel{{ $invoice->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendToAfipModalLabel{{ $invoice->id }}">
                        <i class="fas fa-paper-plane"></i> Enviar factura a AFIP
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas enviar la factura <strong>{{ $invoice->formatted_number }}</strong> a AFIP?</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Una vez enviada, la factura será procesada por AFIP y recibirás un CAE (Código de Autorización Electrónica).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <form action="{{ route('facturacion.send', $invoice->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Enviar a AFIP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection

