<!-- resources/views/distributor_technical_records/show.blade.php -->
@extends('layouts.app')

@section('title', 'Ficha Técnica de Compra')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ficha Técnica de Compra - {{ $distributorClient->name }} {{ $distributorClient->surname }}</h5>
                        <div>

                            <a href="{{ route('distributor-clients.technical-records.edit', [$distributorClient, $distributorTechnicalRecord]) }}"
                               class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="{{ route('distributor-clients.show', $distributorClient) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Información de la Compra</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Fecha de Compra:</strong></td>
                                        <td>{{ $distributorTechnicalRecord->purchase_date->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tipo de Compra:</strong></td>
                                        <td>
                                            @switch($distributorTechnicalRecord->purchase_type)
                                                @case('al_por_mayor')
                                                    Al por Mayor
                                                    @break
                                                @case('al_por_menor')
                                                    Al por Menor
                                                    @break
                                                @case('especial')
                                                    Compra Especial
                                                    @break
                                                @default
                                                    No especificado
                                            @endswitch
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Monto Total:</strong></td>
                                        <td>${{ number_format($distributorTechnicalRecord->total_amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Monto Final:</strong></td>
                                        <td>${{ number_format($distributorTechnicalRecord->final_amount, 2) }}</td>
                                    </tr>
                                    @if($distributorTechnicalRecord->final_amount != $distributorTechnicalRecord->total_amount)
                                    <tr>
                                        <td><strong>Ajuste CC:</strong></td>
                                        <td>
                                            <span class="badge bg-info">
                                                ${{ number_format(abs($distributorTechnicalRecord->total_amount - $distributorTechnicalRecord->final_amount), 2) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                @if($distributorTechnicalRecord->total_amount > $distributorTechnicalRecord->final_amount)
                                                    Se aplicó crédito de cuenta corriente
                                                @else
                                                    Se aplicó deuda de cuenta corriente
                                                @endif
                                            </small>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td><strong>Método de Pago:</strong></td>
                                        <td>
                                            @switch($distributorTechnicalRecord->payment_method)
                                                @case('efectivo')
                                                    Efectivo
                                                    @break
                                                @case('tarjeta')
                                                    Tarjeta
                                                    @break
                                                @case('transferencia')
                                                    Transferencia
                                                    @break
                                                @case('cheque')
                                                    Cheque
                                                    @break
                                                @default
                                                    No especificado
                                            @endswitch
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Información del Cliente</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nombre:</strong></td>
                                        <td>{{ $distributorClient->name }} {{ $distributorClient->surname }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $distributorClient->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Teléfono:</strong></td>
                                        <td>{{ $distributorClient->phone }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Domicilio:</strong></td>
                                        <td>{{ $distributorClient->domicilio ?? 'No registrado' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if(!empty($productsPurchased))
                            <div class="mt-4">
                                <h6 class="text-muted">Productos Comprados</h6>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad</th>
                                                <th>Precio al Mayor</th>
                                                <th>Precio al Menor</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productsPurchased as $item)
                                                <tr>
                                                    <td>{{ $item['product']->product_name }}</td>
                                                    <td>{{ $item['quantity'] }}</td>
                                                    <td>${{ number_format($item['product']->precio_mayor, 2) }}</td>
                                                    <td>${{ number_format($item['product']->precio_menor, 2) }}</td>
                                                    <td>
                                                        @if($distributorTechnicalRecord->purchase_type == 'al_por_mayor')
                                                            ${{ number_format($item['product']->precio_mayor * $item['quantity'], 2) }}
                                                        @else
                                                            ${{ number_format($item['product']->precio_menor * $item['quantity'], 2) }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @if($distributorTechnicalRecord->final_amount != $distributorTechnicalRecord->total_amount)
                            <div class="mt-4">
                                <h6 class="text-muted">Información de Cuenta Corriente</h6>
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <i class="fas fa-calculator me-2"></i>
                                        Detalles del Ajuste de Cuenta Corriente
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Monto del Ajuste:</strong><br>
                                                <span class="badge bg-primary fs-6">
                                                    ${{ number_format(abs($distributorTechnicalRecord->total_amount - $distributorTechnicalRecord->final_amount), 2) }}
                                                </span>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Tipo de Ajuste:</strong><br>
                                                @if($distributorTechnicalRecord->total_amount > $distributorTechnicalRecord->final_amount)
                                                    <span class="badge bg-success">Crédito Aplicado</span>
                                                    <br><small class="text-muted">El cliente usó su crédito de cuenta corriente</small>
                                                @else
                                                    <span class="badge bg-warning">Deuda Aplicada</span>
                                                    <br><small class="text-muted">Se aplicó deuda de cuenta corriente</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <strong>Resumen:</strong><br>
                                                • Total original: ${{ number_format($distributorTechnicalRecord->total_amount, 2) }}<br>
                                                • Ajuste aplicado: ${{ number_format(abs($distributorTechnicalRecord->total_amount - $distributorTechnicalRecord->final_amount), 2) }}<br>
                                                • Monto final a pagar: ${{ number_format($distributorTechnicalRecord->final_amount, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($distributorTechnicalRecord->observations))
                            <div class="mt-4">
                                <h6 class="text-muted">Observaciones</h6>
                                <div class="card">
                                    <div class="card-body">
                                        {!! $distributorTechnicalRecord->observations !!}
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($distributorTechnicalRecord->next_purchase_notes))
                            <div class="mt-4">
                                <h6 class="text-muted">Notas para Próxima Compra</h6>
                                <div class="card">
                                    <div class="card-body">
                                        {{ $distributorTechnicalRecord->next_purchase_notes }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($distributorTechnicalRecord->photos))
                            <div class="mt-4">
                                <h6 class="text-muted">Fotos</h6>
                                <div class="row">
                                    @foreach($distributorTechnicalRecord->photos as $photo)
                                        <div class="col-md-3 mb-3">
                                            <img src="{{ Storage::url($photo) }}" 
                                                 alt="Foto de la compra" 
                                                 class="img-fluid rounded"
                                                 style="max-height: 200px; width: 100%; object-fit: cover;">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-4">
                            <h6 class="text-muted">Información del Sistema</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Creado por:</strong></td>
                                    <td>{{ $distributorTechnicalRecord->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Fecha de creación:</strong></td>
                                    <td>{{ $distributorTechnicalRecord->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Última actualización:</strong></td>
                                    <td>{{ $distributorTechnicalRecord->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('distributor-clients.technical-records.edit', [$distributorClient, $distributorTechnicalRecord]) }}"
                               class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar Ficha Técnica
                            </a>
                            
                            <form action="{{ route('distributor-clients.technical-records.destroy', [$distributorClient, $distributorTechnicalRecord]) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta ficha técnica? Esta acción restaurará el stock de los productos.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Eliminar Ficha Técnica
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 