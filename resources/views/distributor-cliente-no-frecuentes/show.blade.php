@extends('layouts.app')

@section('title', 'Detalles del Cliente No Frecuente - Distribuidora')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> Detalles del Cliente No Frecuente - Distribuidora
                    </h5>
                    <div>
                        <a href="{{ route('distributor-cliente-no-frecuentes.edit', $distributorClienteNoFrecuente) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('distributor-cliente-no-frecuentes.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body px-4">
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
                                @if($distributorClienteNoFrecuente->nombre)
                                    {{ $distributorClienteNoFrecuente->nombre }}
                                @else
                                    <span class="text-muted">Sin nombre registrado</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <div class="form-control-plaintext">
                                @if($distributorClienteNoFrecuente->telefono)
                                    <span class="text-primary">{{ $distributorClienteNoFrecuente->telefono }}</span>
                                @else
                                    <span class="text-muted">Sin teléfono registrado</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Venta -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-shopping-cart"></i> Información de la Venta
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Fecha de la Venta</label>
                            <div class="form-control-plaintext">
                                <span class="text-primary fs-5 fw-bold">{{ $distributorClienteNoFrecuente->fecha->format('d/m/Y') }}</span>
                            </div>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted">Valor de la Venta</label>
                            <div class="form-control-plaintext">
                                <span class="fw-bold text-success fs-4">${{ number_format($distributorClienteNoFrecuente->monto, 2) }}</span>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted">Tipo de Compra</label>
                            <div class="form-control-plaintext">
                                @if($distributorClienteNoFrecuente->purchase_type)
                                    <span class="badge bg-primary fs-6 px-3 py-2">
                                        <i class="fas fa-tags me-1"></i>
                                        {{ $distributorClienteNoFrecuente->purchase_type === 'al_por_mayor' ? 'Al por Mayor' : 'Al por Menor' }}
                                    </span>
                                @else
                                    <span class="text-muted fst-italic">No especificado</span>
                                @endif
                            </div>
                        </div>

                        @if($distributorClienteNoFrecuente->observaciones)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <div class="form-control-plaintext">
                                {{ $distributorClienteNoFrecuente->observaciones }}
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Productos Comprados -->
                    @if($distributorClienteNoFrecuente->products_purchased && count($distributorClienteNoFrecuente->products_purchased) > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-shopping-cart"></i> Productos Comprados
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th class="fw-bold"><i class="fas fa-box me-2"></i>Producto</th>
                                            <th class="fw-bold text-center"><i class="fas fa-hashtag me-2"></i>Cantidad</th>
                                            <th class="fw-bold text-end"><i class="fas fa-dollar-sign me-2"></i>Precio Unitario</th>
                                            <th class="fw-bold text-end"><i class="fas fa-calculator me-2"></i>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($distributorClienteNoFrecuente->products_purchased as $product)
                                            @php
                                                $supplierInventory = \App\Models\SupplierInventory::find($product['product_id']);
                                            @endphp
                                            <tr class="align-middle">
                                                <td>
                                                    @if($supplierInventory)
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3">
                                                                <i class="fas fa-box text-primary fs-5"></i>
                                                            </div>
                                                            <div>
                                                                <strong class="text-dark">{{ $supplierInventory->product_name }}</strong>
                                                                @if($supplierInventory->description)
                                                                    <br><small class="text-muted">{{ $supplierInventory->description }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted fst-italic">Producto no encontrado</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary fs-6 px-3 py-2">{{ $product['quantity'] }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-success fw-bold fs-6">${{ number_format($product['price'], 2) }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="text-success fw-bold fs-5">${{ number_format($product['quantity'] * $product['price'], 2) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-shopping-cart"></i> Productos
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Productos</label>
                            <div class="form-control-plaintext">
                                @if($distributorClienteNoFrecuente->productos)
                                    {{ $distributorClienteNoFrecuente->productos }}
                                @else
                                    <span class="text-muted fst-italic">Sin productos especificados</span>
                                @endif
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
                            <label class="form-label fw-bold text-muted">Registrado por</label>
                            <div class="form-control-plaintext">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    <span class="text-dark fw-bold fs-6">{{ $distributorClienteNoFrecuente->user->name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted">Fecha de registro</label>
                            <div class="form-control-plaintext">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <span class="text-dark fw-bold fs-6">{{ $distributorClienteNoFrecuente->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        @if($distributorClienteNoFrecuente->updated_at->ne($distributorClienteNoFrecuente->created_at))
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted">Última actualización</label>
                            <div class="form-control-plaintext">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-edit text-primary me-2"></i>
                                    <span class="text-dark fw-bold fs-6">{{ $distributorClienteNoFrecuente->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
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
                                    <a href="{{ route('distributor-cliente-no-frecuentes.edit', $distributorClienteNoFrecuente) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="{{ route('distributor-cliente-no-frecuentes.index') }}" class="btn btn-secondary">
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
                                {{ $distributorClienteNoFrecuente->nombre ?: 'Cliente sin nombre' }}
                            </h6>
                            <p class="card-text mb-1">
                                <strong>Fecha:</strong> {{ $distributorClienteNoFrecuente->fecha->format('d/m/Y') }}
                            </p>
                            <p class="card-text mb-1">
                                <strong>Distribuidor:</strong> {{ $distributorClienteNoFrecuente->distribuidor }}
                            </p>
                            <p class="card-text">
                                <strong>Monto:</strong> ${{ number_format($distributorClienteNoFrecuente->monto, 2) }}
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
                <form action="{{ route('distributor-cliente-no-frecuentes.destroy', $distributorClienteNoFrecuente) }}" method="POST" style="display: inline;">
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
