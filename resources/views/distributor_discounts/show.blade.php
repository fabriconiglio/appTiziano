@extends('layouts.app')

@section('title', 'Detalle del Descuento - Distribuidores')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-eye"></i> Detalle del Descuento</h1>
        <div>
            <a href="{{ route('distributor-discounts.edit', $distributorDiscount) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('distributor-discounts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Información Principal -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información del Descuento</h5>
                    <span class="badge {{ $distributorDiscount->status_badge_class }} fs-6">
                        {{ $distributorDiscount->status }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-1">Descripción</h6>
                            <p class="fw-bold">{{ $distributorDiscount->description }}</p>
                            
                            <h6 class="text-muted mb-1 mt-3">Distribuidor{{ !empty($distributorDiscount->distributor_client_ids) && count($distributorDiscount->distributor_client_ids) > 1 ? 'es' : '' }}</h6>
                            @if(!empty($distributorDiscount->distributor_client_ids))
                                @php
                                    $distributorClients = \App\Models\DistributorClient::whereIn('id', $distributorDiscount->distributor_client_ids)->get();
                                @endphp
                                @foreach($distributorClients as $index => $client)
                                    <p class="fw-bold mb-1">{{ $client->full_name }}</p>
                                    <small class="text-muted d-block">{{ $client->email ?? 'Sin email' }}</small>
                                    @if(!$loop->last)<hr class="my-2">@endif
                                @endforeach
                            @else
                                <p class="fw-bold">{{ $distributorDiscount->distributorClient->full_name }}</p>
                                <small class="text-muted">{{ $distributorDiscount->distributorClient->email ?? 'Sin email' }}</small>
                            @endif
                            
                            <h6 class="text-muted mb-1 mt-3">Tipo de Descuento</h6>
                            <span class="badge bg-info fs-6">{{ $distributorDiscount->discount_type_text }}</span>
                        </div>
                        
                        <div class="col-md-6">
                            @if($distributorDiscount->discount_type !== 'gift')
                                <h6 class="text-muted mb-1">Valor del Descuento</h6>
                                <p class="fw-bold fs-4 text-primary">
                                    @if($distributorDiscount->discount_type === 'percentage')
                                        {{ $distributorDiscount->discount_value }}%
                                    @else
                                        ${{ number_format($distributorDiscount->discount_value, 2) }}
                                    @endif
                                </p>
                            @endif
                            
                            <h6 class="text-muted mb-1 mt-3">Cantidad Mínima</h6>
                            <p class="fw-bold">{{ $distributorDiscount->minimum_quantity }}</p>
                            
                            @if($distributorDiscount->minimum_amount)
                                <h6 class="text-muted mb-1 mt-3">Monto Mínimo</h6>
                                <p class="fw-bold">${{ number_format($distributorDiscount->minimum_amount, 2) }}</p>
                            @endif
                        </div>
                    </div>
                    
                    @if($distributorDiscount->conditions)
                        <hr>
                        <h6 class="text-muted mb-1">Condiciones Especiales</h6>
                        <p class="text-break">{{ $distributorDiscount->conditions }}</p>
                    @endif
                </div>
            </div>

            <!-- Aplicabilidad del Producto -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Aplicabilidad del Producto</h5>
                </div>
                <div class="card-body">
                    @if($distributorDiscount->applies_to_all_products)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Aplica a todos los productos del distribuidor</strong>
                        </div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @if($distributorDiscount->applies_to_category && $distributorDiscount->category)
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-tags"></i>
                                    <strong>Aplica por categoría:</strong> {{ $distributorDiscount->category->name }}
                                </div>
                            @endif
                            @if($distributorDiscount->applies_to_brand && $distributorDiscount->brand)
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-tag"></i>
                                    <strong>Aplica por marca:</strong> {{ $distributorDiscount->brand->name }}
                                </div>
                            @endif
                        </div>
                        @if(!empty($distributorDiscount->supplier_inventory_ids))
                            <h6 class="text-muted mb-1">Productos del Inventario</h6>
                            @php
                                $supplierInventories = \App\Models\SupplierInventory::whereIn('id', $distributorDiscount->supplier_inventory_ids)->get();
                            @endphp
                            @foreach($supplierInventories as $inventory)
                                <div class="card bg-light mb-2">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $inventory->product_name }}</h6>
                                        <p class="card-text mb-1">
                                            <strong>Precio Mayor:</strong> ${{ number_format($inventory->precio_mayor, 2) }}
                                        </p>
                                        <p class="card-text mb-0">
                                            <strong>Stock:</strong> {{ $inventory->stock_quantity }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        @elseif($distributorDiscount->supplierInventory)
                            <h6 class="text-muted mb-1">Producto del Inventario</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $distributorDiscount->supplierInventory->product_name }}</h6>
                                    <p class="card-text mb-1">
                                        <strong>SKU:</strong> {{ $distributorDiscount->supplierInventory->sku }}
                                    </p>
                                    <p class="card-text mb-1">
                                        <strong>Precio Mayor:</strong> ${{ number_format($distributorDiscount->supplierInventory->precio_mayor, 2) }}
                                    </p>
                                    <p class="card-text mb-0">
                                        <strong>Stock:</strong> {{ $distributorDiscount->supplierInventory->stock_quantity }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="row">
                                @if($distributorDiscount->product_name)
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1">Nombre del Producto</h6>
                                        <p class="fw-bold">{{ $distributorDiscount->product_name }}</p>
                                    </div>
                                @endif
                                
                                @if($distributorDiscount->product_sku)
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1">SKU del Producto</h6>
                                        <p class="fw-bold">{{ $distributorDiscount->product_sku }}</p>
                                    </div>
                                @endif
                            </div>
                            
                            @if(!$distributorDiscount->product_name && !$distributorDiscount->product_sku)
                                <div class="alert alert-warning" style="margin-top: 10px;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Producto no especificado</strong>
                                </div>
                            @endif
                        @endif
                    @endif
                </div>
            </div>

            <!-- Productos de Regalo (solo si es tipo gift) -->
            @if($distributorDiscount->discount_type === 'gift' && $distributorDiscount->gift_products)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-gift"></i> Productos de Regalo</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach($distributorDiscount->gift_products as $giftProduct)
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-gift text-success me-2"></i>
                                    {{ $giftProduct }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="col-md-4">
            <!-- Estado y Validez -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-toggle-on"></i> Estado y Validez</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Estado</h6>
                        <span class="badge {{ $distributorDiscount->status_badge_class }} fs-6">
                            {{ $distributorDiscount->status }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Activo</h6>
                        @if($distributorDiscount->is_active)
                            <span class="badge bg-success"><i class="fas fa-check"></i> Sí</span>
                        @else
                            <span class="badge bg-danger"><i class="fas fa-times"></i> No</span>
                        @endif
                    </div>
                    
                    @if($distributorDiscount->valid_from || $distributorDiscount->valid_until)
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Vigencia</h6>
                            @if($distributorDiscount->valid_from)
                                <p class="mb-1 small">
                                    <strong>Desde:</strong> {{ $distributorDiscount->valid_from->format('d/m/Y') }}
                                </p>
                            @endif
                            @if($distributorDiscount->valid_until)
                                <p class="mb-0 small">
                                    <strong>Hasta:</strong> {{ $distributorDiscount->valid_until->format('d/m/Y') }}
                                </p>
                            @endif
                        </div>
                    @endif
                    
                    <div class="mb-0">
                        <h6 class="text-muted mb-1">Validez</h6>
                        @if($distributorDiscount->isValid())
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Válido</span>
                        @else
                            <span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> No válido</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estadísticas de Uso -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Estadísticas de Uso</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-1">Usos Actuales</h6>
                        <p class="fw-bold fs-4 text-primary mb-0">{{ $distributorDiscount->current_uses }}</p>
                    </div>
                    
                    @if($distributorDiscount->max_uses)
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Límite de Usos</h6>
                            <p class="fw-bold mb-2">{{ $distributorDiscount->max_uses }}</p>
                            
                            @php
                                $percentage = ($distributorDiscount->current_uses / $distributorDiscount->max_uses) * 100;
                            @endphp
                            <div class="progress" style="height: 10px;">
                                            @php 
                                                $barWidth = min($percentage, 100);
                                                $barStyle = "width: {$barWidth}%";
                                            @endphp
                                            <div class="progress-bar @if($percentage < 50) bg-success @elseif($percentage < 80) bg-warning @else bg-danger @endif" style="{{ $barStyle }}"></div>
                            </div>
                            <small class="text-muted">{{ number_format($percentage, 1) }}% utilizado</small>
                        </div>
                    @else
                        <div class="mb-3">
                            <h6 class="text-muted mb-1">Límite de Usos</h6>
                            <span class="badge bg-info"><i class="fas fa-infinity"></i> Sin límite</span>
                        </div>
                    @endif
                    
                    @if($distributorDiscount->max_uses && $distributorDiscount->current_uses >= $distributorDiscount->max_uses)
                        <div class="alert alert-danger small mb-0">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Límite alcanzado:</strong> Este descuento ya no se puede usar.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('distributor-discounts.edit', $distributorDiscount) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar Descuento
                        </a>
                        
                        <form action="{{ route('distributor-discounts.toggle-status', $distributorDiscount) }}" 
                              method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn {{ $distributorDiscount->is_active ? 'btn-secondary' : 'btn-success' }} w-100">
                                <i class="fas {{ $distributorDiscount->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                {{ $distributorDiscount->is_active ? 'Desactivar' : 'Activar' }}
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Fechas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Creado</h6>
                            <p class="mb-0">{{ $distributorDiscount->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">Última Modificación</h6>
                            <p class="mb-0">{{ $distributorDiscount->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-1">ID del Descuento</h6>
                            <p class="mb-0 font-monospace">#{{ $distributorDiscount->id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
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
                    <p class="mb-2">¿Estás seguro de que quieres eliminar este descuento?</p>
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">{{ $distributorDiscount->description }}</h6>
                            <p class="card-text mb-1">
                                <strong>Distribuidor:</strong> {{ $distributorDiscount->distributorClient->full_name }}
                            </p>
                            <p class="card-text mb-1">
                                <strong>Tipo:</strong> {{ $distributorDiscount->discount_type_text }}
                            </p>
                            <p class="card-text">
                                <strong>Usos:</strong> {{ $distributorDiscount->current_uses }}
                                @if($distributorDiscount->max_uses)
                                    / {{ $distributorDiscount->max_uses }}
                                @endif
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
                <form action="{{ route('distributor-discounts.destroy', $distributorDiscount) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Eliminar Descuento
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection