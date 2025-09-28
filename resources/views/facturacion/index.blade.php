@extends('layouts.app')

@section('title', 'Facturación AFIP')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice"></i> Facturación AFIP
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('facturacion.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nueva Factura
                        </a>
                        <a href="{{ route('facturacion.configuration') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-cog"></i> Configuración
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filtros -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviada</option>
                                    <option value="authorized" {{ request('status') == 'authorized' ? 'selected' : '' }}>Autorizada</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="invoice_type" class="form-control form-control-sm">
                                    <option value="">Todos los tipos</option>
                                    <option value="A" {{ request('invoice_type') == 'A' ? 'selected' : '' }}>Factura A</option>
                                    <option value="B" {{ request('invoice_type') == 'B' ? 'selected' : '' }}>Factura B</option>
                                    <option value="C" {{ request('invoice_type') == 'C' ? 'selected' : '' }}>Factura C</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control form-control-sm" 
                                       value="{{ request('date_from') }}" placeholder="Desde">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control form-control-sm" 
                                       value="{{ request('date_to') }}" placeholder="Hasta">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="{{ route('facturacion.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>

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
                                    <td>{{ $invoice->distributorClient->full_name }}</td>
                                    <td>
                                        <span class="badge badge-info">Factura {{ $invoice->invoice_type }}</span>
                                    </td>
                                    <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                    <td>${{ number_format($invoice->total, 2, ',', '.') }}</td>
                                    <td>
                                        @switch($invoice->status)
                                            @case('draft')
                                                <span class="badge badge-secondary">Borrador</span>
                                                @break
                                            @case('sent')
                                                <span class="badge badge-warning">Enviada</span>
                                                @break
                                            @case('authorized')
                                                <span class="badge badge-success">Autorizada</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge badge-danger">Rechazada</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-dark">Cancelada</span>
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
                                                <form action="{{ route('facturacion.send', $invoice->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            title="Enviar a AFIP" 
                                                            onclick="return confirm('¿Enviar factura a AFIP?')">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
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
@endsection

@push('scripts')
<script>
    // Auto-submit del formulario de filtros
    document.querySelectorAll('select[name="status"], select[name="invoice_type"]').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
