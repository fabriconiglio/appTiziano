@extends('layouts.app')

@section('title', 'Factura AFIP #' . $facturacion->formatted_number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice"></i> Factura {{ $facturacion->formatted_number }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('facturacion.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        
                        @if($facturacion->status === 'authorized')
                            <a href="{{ route('facturacion.download-pdf', $facturacion->id) }}" 
                               class="btn btn-primary btn-sm" target="_blank">
                                <i class="fas fa-file-pdf"></i> Descargar PDF
                            </a>
                        @endif
                        
                        @if(in_array($facturacion->status, ['draft', 'rejected']))
                            <button type="button" class="btn btn-success btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#sendToAfipModal">
                                <i class="fas fa-paper-plane"></i> 
                                {{ $facturacion->status === 'rejected' ? 'Re-enviar a AFIP' : 'Enviar a AFIP' }}
                            </button>
                        @endif
                        
                        @if($facturacion->canBeCancelled())
                            <form action="{{ route('facturacion.cancel', $facturacion->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm" 
                                        onclick="return confirm('¿Cancelar factura?')">
                                    <i class="fas fa-ban"></i> Cancelar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información de la factura -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle"></i> Información de la Factura</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Número:</strong></td>
                                    <td>{{ $facturacion->formatted_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tipo:</strong></td>
                                    <td>Factura {{ $facturacion->invoice_type }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha:</strong></td>
                                    <td>{{ $facturacion->invoice_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        @switch($facturacion->status)
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
                                </tr>
                                @if($facturacion->cae)
                                <tr>
                                    <td><strong>CAE:</strong></td>
                                    <td><code>{{ $facturacion->cae }}</code></td>
                                </tr>
                                @endif
                                @if($facturacion->cae_expiration)
                                <tr>
                                    <td><strong>Vencimiento CAE:</strong></td>
                                    <td>{{ $facturacion->cae_expiration->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fas fa-user"></i> Información del Cliente</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $facturacion->distributorClient->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Apellido:</strong></td>
                                    <td>{{ $facturacion->distributorClient->surname }}</td>
                                </tr>
                                <tr>
                                    <td><strong>DNI:</strong></td>
                                    <td>{{ $facturacion->distributorClient->dni }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $facturacion->distributorClient->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>{{ $facturacion->distributorClient->phone }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Domicilio:</strong></td>
                                    <td>{{ $facturacion->distributorClient->domicilio ?? 'No especificado' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Items de la factura -->
                    <div class="row">
                        <div class="col-12">
                            <h5><i class="fas fa-list"></i> Productos</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-right">Precio Unit.</th>
                                            <th class="text-right">Subtotal</th>
                                            <th class="text-right">IVA</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($facturacion->items as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->description }}</strong><br>
                                                <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-right">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                            <td class="text-right">${{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                            <td class="text-right">${{ number_format($item->tax_amount, 2, ',', '.') }}</td>
                                            <td class="text-right">${{ number_format($item->total, 2, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                            <td class="text-right"><strong>${{ number_format($facturacion->subtotal, 2, ',', '.') }}</strong></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-right"><strong>IVA (21%):</strong></td>
                                            <td class="text-right"><strong>${{ number_format($facturacion->tax_amount, 2, ',', '.') }}</strong></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                            <td></td>
                                            <td class="text-right"><strong>${{ number_format($facturacion->total, 2, ',', '.') }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    @if($facturacion->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><i class="fas fa-sticky-note"></i> Notas</h5>
                            <div class="alert alert-info">
                                {{ $facturacion->notes }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Respuesta de AFIP -->
                    @if($facturacion->afip_response)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><i class="fas fa-server"></i> Respuesta de AFIP</h5>
                            <div class="card {{ $facturacion->status === 'rejected' ? 'border-danger' : '' }}">
                                <div class="card-header {{ $facturacion->status === 'rejected' ? 'bg-danger text-white' : 'bg-success text-white' }}">
                                    <strong>
                                        @if($facturacion->status === 'rejected')
                                            <i class="fas fa-exclamation-triangle"></i> Error al procesar la factura
                                        @else
                                            <i class="fas fa-check-circle"></i> Factura procesada correctamente
                                        @endif
                                    </strong>
                                </div>
                                <div class="card-body">
                                    @if($facturacion->status === 'rejected')
                                        <div class="alert alert-danger">
                                            <strong>Error:</strong> 
                                            @if(isset($facturacion->afip_response['error']))
                                                {{ $facturacion->afip_response['error'] }}
                                            @else
                                                La factura fue rechazada por AFIP
                                            @endif
                                        </div>
                                    @endif
                                    <pre class="mb-0"><code>{{ json_encode($facturacion->afip_response, JSON_PRETTY_PRINT) }}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Creada: {{ $facturacion->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                <i class="fas fa-edit"></i> Actualizada: {{ $facturacion->updated_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para enviar a AFIP -->
@if(in_array($facturacion->status, ['draft', 'rejected']))
<div class="modal fade" id="sendToAfipModal" tabindex="-1" role="dialog" aria-labelledby="sendToAfipModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendToAfipModalLabel">
                    <i class="fas fa-paper-plane"></i> {{ $facturacion->status === 'rejected' ? 'Re-enviar factura a AFIP' : 'Enviar factura a AFIP' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas {{ $facturacion->status === 'rejected' ? 'reenviar' : 'enviar' }} la factura <strong>{{ $facturacion->formatted_number }}</strong> a AFIP?</p>
                @if($facturacion->status === 'rejected')
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Esta factura fue rechazada anteriormente. Se reseteará el estado antes de reenviarla.
                    </div>
                @endif
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Una vez enviada, la factura será procesada por AFIP y recibirás un CAE (Código de Autorización Electrónica).
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form action="{{ route('facturacion.send', $facturacion->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane"></i> {{ $facturacion->status === 'rejected' ? 'Re-enviar' : 'Enviar' }} a AFIP
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
