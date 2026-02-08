@extends('layouts.app')

@section('title', 'Editar Factura #' . $facturacion->formatted_number)

@section('content')
<div class="container-fluid">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-edit"></i> Editar Factura {{ $facturacion->formatted_number }}</h1>
        <div>
            <a href="{{ route('facturacion.show', $facturacion->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="{{ route('facturacion.update', $facturacion->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <!-- Información de la factura (solo lectura) -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-file-invoice me-2"></i>Información de la Factura
                            </h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Número:</strong> {{ $facturacion->formatted_number }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Tipo:</strong> Factura {{ $facturacion->invoice_type }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Fecha:</strong> {{ $facturacion->invoice_date->format('d/m/Y') }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Cliente:</strong> {{ $facturacion->client_full_name }}
                                </div>
                            </div>
                        </div>

                        <!-- Items editables -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-list me-2"></i>Productos / Servicios
                            </h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i>
                                Puede modificar la descripción de cada producto o servicio. La cantidad y el precio no se pueden cambiar.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40%">Descripción</th>
                                            <th width="10%" class="text-center">Cantidad</th>
                                            <th width="15%" class="text-end">Precio Unit.</th>
                                            <th width="15%" class="text-end">IVA</th>
                                            <th width="15%" class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($facturacion->items as $index => $item)
                                        <tr>
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                <input type="text" 
                                                       name="items[{{ $index }}][description]" 
                                                       class="form-control @error('items.' . $index . '.description') is-invalid @enderror"
                                                       value="{{ old('items.' . $index . '.description', $item->description) }}" 
                                                       required>
                                                @error('items.' . $index . '.description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="text-center align-middle">{{ $item->quantity }}</td>
                                            <td class="text-end align-middle">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                            <td class="text-end align-middle">${{ number_format($item->tax_amount, 2, ',', '.') }}</td>
                                            <td class="text-end align-middle">${{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        @php
                                            $neto = $facturacion->subtotal / 1.21;
                                        @endphp
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Neto:</strong></td>
                                            <td class="text-end"><strong>${{ number_format($neto, 2, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>IVA 21%:</strong></td>
                                            <td class="text-end"><strong>${{ number_format($facturacion->tax_amount, 2, ',', '.') }}</strong></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                            <td class="text-end"><strong>${{ number_format($facturacion->total, 2, ',', '.') }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light py-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                            <a href="{{ route('facturacion.show', $facturacion->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
