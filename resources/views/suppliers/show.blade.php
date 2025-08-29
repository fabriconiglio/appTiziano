@extends('layouts.app')

@section('title', 'Detalle del Proveedor')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <!-- Información del Proveedor -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> {{ $supplier->name }}
                    </h5>
                    <div>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle"></i> Información Básica
                            </h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $supplier->name }}</td>
                                </tr>
                                @if($supplier->business_name)
                                <tr>
                                    <td><strong>Razón Social:</strong></td>
                                    <td>{{ $supplier->business_name }}</td>
                                </tr>
                                @endif
                                @if($supplier->cuit)
                                <tr>
                                    <td><strong>CUIT:</strong></td>
                                    <td>{{ $supplier->cuit }}</td>
                                </tr>
                                @endif
                                @if($supplier->tax_category)
                                <tr>
                                    <td><strong>Categoría Impositiva:</strong></td>
                                    <td>{{ $supplier->tax_category }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        <span class="badge {{ $supplier->status_badge_class }}">
                                            {{ $supplier->status_text }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-address-book"></i> Información de Contacto
                            </h6>
                            <table class="table table-borderless">
                                @if($supplier->contact_person)
                                <tr>
                                    <td><strong>Contacto:</strong></td>
                                    <td>{{ $supplier->contact_person }}</td>
                                </tr>
                                @endif
                                @if($supplier->email)
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($supplier->phone)
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>
                                        <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($supplier->website)
                                <tr>
                                    <td><strong>Sitio Web:</strong></td>
                                    <td>
                                        <a href="{{ $supplier->website }}" target="_blank">{{ $supplier->website }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($supplier->address)
                                <tr>
                                    <td><strong>Dirección:</strong></td>
                                    <td>{{ $supplier->address }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <!-- Condiciones Comerciales -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-handshake"></i> Condiciones Comerciales
                            </h6>
                            <table class="table table-borderless">
                                @if($supplier->payment_terms)
                                <tr>
                                    <td><strong>Condiciones de Pago:</strong></td>
                                    <td>{{ $supplier->payment_terms }}</td>
                                </tr>
                                @endif
                                @if($supplier->delivery_time)
                                <tr>
                                    <td><strong>Tiempo de Entrega:</strong></td>
                                    <td>{{ $supplier->delivery_time }}</td>
                                </tr>
                                @endif
                                @if($supplier->minimum_order)
                                <tr>
                                    <td><strong>Pedido Mínimo:</strong></td>
                                    <td>${{ number_format($supplier->minimum_order, 2, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if($supplier->discount_percentage)
                                <tr>
                                    <td><strong>Descuento:</strong></td>
                                    <td>{{ $supplier->discount_percentage }}%</td>
                                </tr>
                                @endif
                                @if($supplier->bank_account)
                                <tr>
                                    <td><strong>Cuenta Bancaria:</strong></td>
                                    <td>{{ $supplier->bank_account }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <!-- Información Adicional -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-sticky-note"></i> Información Adicional
                            </h6>
                            @if($supplier->notes)
                                <p>{{ $supplier->notes }}</p>
                            @else
                                <p class="text-muted">No hay notas adicionales.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos del Proveedor -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-boxes"></i> Productos del Proveedor
                    </h6>
                </div>
                <div class="card-body">
                    @if($supplier->supplierInventories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock</th>
                                        <th>Precio Mayor</th>
                                        <th>Precio Menor</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->supplierInventories->take(10) as $product)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $product->product_name }}</div>
                                                @if($product->description)
                                                    <small class="text-muted">{{ $product->description }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $product->stock_quantity > 5 ? 'bg-success' : ($product->stock_quantity > 0 ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ $product->stock_quantity }}
                                                </span>
                                            </td>
                                            <td>${{ number_format($product->precio_mayor ?? 0, 2, ',', '.') }}</td>
                                            <td>${{ number_format($product->precio_menor ?? 0, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="badge {{ $product->status_badge_class }}">
                                                    {{ $product->status_text }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($supplier->supplierInventories->count() > 10)
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Mostrando 10 de {{ $supplier->supplierInventories->count() }} productos
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No hay productos registrados para este proveedor</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar con Estadísticas -->
        <div class="col-md-4">
            <!-- Estadísticas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <h4 class="mb-0">{{ $stats['total_products'] }}</h4>
                                    <small>Productos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body py-2">
                                    <h4 class="mb-0">${{ number_format($stats['total_value'], 2, ',', '.') }}</h4>
                                    <small>Valor Total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body py-2">
                                    <h4 class="mb-0">{{ $stats['low_stock_products'] }}</h4>
                                    <small>Bajo Stock</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body py-2">
                                    <h4 class="mb-0">{{ $stats['out_of_stock_products'] }}</h4>
                                    <small>Sin Stock</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('supplier-inventories.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </a>
                        <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $supplier->is_active ? 'warning' : 'success' }} btn-sm w-100">
                                <i class="fas fa-{{ $supplier->is_active ? 'pause' : 'play' }}"></i>
                                {{ $supplier->is_active ? 'Desactivar' : 'Activar' }} Proveedor
                            </button>
                        </form>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" 
                              onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-trash"></i> Eliminar Proveedor
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 