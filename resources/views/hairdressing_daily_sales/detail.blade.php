@extends('layouts.app')

@section('title', 'Detalles de Ventas - Peluquería')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 text-dark mb-2">
                Detalles de Ventas - Peluquería
                @if($startDate->ne($endDate))
                    - {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}
                @else
                    - {{ $startDate->format('d/m/Y') }}
                @endif
            </h1>
            <p class="text-muted">
                Detalle de {{ ucfirst(str_replace('_', ' ', $category)) }}
                @if($startDate->ne($endDate))
                    del período {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}
                @else
                    para el {{ $startDate->format('d/m/Y') }}
                @endif
            </p>
        </div>
    </div>

    <!-- Botón de regreso -->
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('hairdressing-daily-sales.index', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Ventas
            </a>
        </div>
    </div>

    <!-- Tabla de detalles -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        @switch($category)
                            @case('client_accounts')
                                <i class="fas fa-calculator me-2"></i>Cuentas Corrientes
                                @break
                            @case('technical_records')
                                <i class="fas fa-scissors me-2"></i>Servicios
                                @break
                            @case('product_sales')
                                <i class="fas fa-box me-2"></i>Ventas de Productos
                                @break
                            @case('cliente_no_frecuente')
                                <i class="fas fa-user-clock me-2"></i>Clientes No Frecuentes
                                @break
                            @default
                                <i class="fas fa-list me-2"></i>Detalles
                        @endswitch
                    </h5>
                </div>
                <div class="card-body">
                    @if($category === 'total')
                        <!-- Vista especial para el total -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-calculator me-2"></i>Cuentas Corrientes ({{ $data['client_accounts']->count() }})
                                </h6>
                                @if($data['client_accounts']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Fecha</th>
                                                    <th>Monto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['client_accounts']->take(5) as $item)
                                                    <tr>
                                                        <td>{{ $item->client->name ?? 'Sin cliente' }}</td>
                                                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                                        <td>${{ number_format($item->amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($data['client_accounts']->count() > 5)
                                        <small class="text-muted">Y {{ $data['client_accounts']->count() - 5 }} más...</small>
                                    @endif
                                @else
                                    <p class="text-muted">No hay cuentas corrientes</p>
                                @endif
                            </div>

                            <div class="col-md-6 mb-4">
                                <h6 class="text-info mb-3">
                                    <i class="fas fa-scissors me-2"></i>Servicios ({{ $data['technical_records']->count() }})
                                </h6>
                                @if($data['technical_records']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Fecha</th>
                                                    <th>Costo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['technical_records']->take(5) as $item)
                                                    <tr>
                                                        <td>{{ $item->client->name ?? 'Sin cliente' }}</td>
                                                        <td>{{ $item->service_date->format('d/m/Y') }}</td>
                                                        <td>${{ number_format($item->service_cost, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($data['technical_records']->count() > 5)
                                        <small class="text-muted">Y {{ $data['technical_records']->count() - 5 }} más...</small>
                                    @endif
                                @else
                                    <p class="text-muted">No hay servicios</p>
                                @endif
                            </div>

                            <div class="col-md-6 mb-4">
                                <h6 class="text-warning mb-3">
                                    <i class="fas fa-box me-2"></i>Productos ({{ $data['product_sales']->count() }})
                                </h6>
                                @if($data['product_sales']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Fecha</th>
                                                    <th>Cantidad</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['product_sales']->take(5) as $item)
                                                    <tr>
                                                        <td>{{ $item->product_name ?? 'Sin nombre' }}</td>
                                                        <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                                        <td>{{ $item->quantity }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($data['product_sales']->count() > 5)
                                        <small class="text-muted">Y {{ $data['product_sales']->count() - 5 }} más...</small>
                                    @endif
                                @else
                                    <p class="text-muted">No hay ventas de productos</p>
                                @endif
                            </div>

                            <div class="col-md-6 mb-4">
                                <h6 class="text-secondary mb-3">
                                    <i class="fas fa-user-clock me-2"></i>Clientes No Frecuentes ({{ $data['cliente_no_frecuente']->count() }})
                                </h6>
                                @if($data['cliente_no_frecuente']->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Cliente</th>
                                                    <th>Fecha</th>
                                                    <th>Valor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data['cliente_no_frecuente']->take(5) as $item)
                                                    <tr>
                                                        <td>{{ $item->nombre ?: 'Sin nombre' }}</td>
                                                        <td>{{ $item->fecha->format('d/m/Y') }}</td>
                                                        <td>${{ number_format($item->monto, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($data['cliente_no_frecuente']->count() > 5)
                                        <small class="text-muted">Y {{ $data['cliente_no_frecuente']->count() - 5 }} más...</small>
                                    @endif
                                @else
                                    <p class="text-muted">No hay clientes no frecuentes</p>
                                @endif
                            </div>
                        </div>
                    @elseif($data->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        @switch($category)
                                            @case('client_accounts')
                                                <th>Cliente</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                <th>Descripción</th>
                                                @break
                                            @case('technical_records')
                                                <th>Cliente</th>
                                                <th>Fecha Servicio</th>
                                                <th>Servicio</th>
                                                <th>Costo</th>
                                                @break
                                            @case('product_sales')
                                                <th>Producto</th>
                                                <th>Fecha</th>
                                                <th>Cantidad</th>
                                                <th>Precio Unit.</th>
                                                <th>Total</th>
                                                @break
                                            @case('cliente_no_frecuente')
                                                <th>Cliente</th>
                                                <th>Fecha</th>
                                                <th>Peluquero</th>
                                                <th>Servicios</th>
                                                <th>Valor</th>
                                                @break
                                        @endswitch
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                        <tr>
                                            @switch($category)
                                                @case('client_accounts')
                                                    <td>
                                                        <div class="fw-bold">{{ $item->client->name ?? 'Sin cliente' }}</div>
                                                        <small class="text-muted">{{ $item->client->email ?? '' }}</small>
                                                    </td>
                                                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        <span class="fw-bold text-success">${{ number_format($item->amount, 2) }}</span>
                                                    </td>
                                                    <td>{{ $item->description ?? 'Sin descripción' }}</td>
                                                    @break
                                                @case('technical_records')
                                                    <td>
                                                        <div class="fw-bold">{{ $item->client->name ?? 'Sin cliente' }}</div>
                                                        <small class="text-muted">{{ $item->client->email ?? '' }}</small>
                                                    </td>
                                                    <td>{{ $item->service_date->format('d/m/Y') }}</td>
                                                    <td>
                                                        <div class="fw-bold">{{ $item->service_type ?? 'Sin tipo' }}</div>
                                                        @if($item->hair_treatments)
                                                            <small class="text-muted">{{ $item->hair_treatments }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-info">${{ number_format($item->service_cost, 2) }}</span>
                                                    </td>
                                                    @break
                                                @case('product_sales')
                                                    <td>
                                                        <div class="fw-bold">{{ $item->product_name ?? 'Sin nombre' }}</div>
                                                        @if($item->description)
                                                            <small class="text-muted">{{ $item->description }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        <span class="badge bg-primary">{{ $item->quantity }}</span>
                                                    </td>
                                                    <td>${{ number_format($item->price ?? 0, 2) }}</td>
                                                    <td>
                                                        <span class="fw-bold text-warning">${{ number_format(($item->quantity * ($item->price ?? 0)), 2) }}</span>
                                                    </td>
                                                    @break
                                                @case('cliente_no_frecuente')
                                                    <td>
                                                        <div class="fw-bold">{{ $item->nombre ?: 'Sin nombre' }}</div>
                                                        @if($item->telefono)
                                                            <small class="text-muted">{{ $item->telefono }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->fecha->format('d/m/Y') }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $item->peluquero }}</span>
                                                    </td>
                                                    <td>
                                                        @if($item->servicios)
                                                            {{ Str::limit($item->servicios, 50) }}
                                                        @else
                                                            <span class="text-muted">Sin servicios</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-secondary">${{ number_format($item->monto, 2) }}</span>
                                                    </td>
                                                    @break
                                            @endswitch
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-4x mb-3"></i>
                                <h5>No hay registros para mostrar</h5>
                                <p class="mb-0">No se encontraron datos para la categoría seleccionada en el período especificado</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    @if($category === 'total' ? ($data['client_accounts']->count() > 0 || $data['technical_records']->count() > 0 || $data['product_sales']->count() > 0 || $data['cliente_no_frecuente']->count() > 0) : $data->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Resumen
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">
                                    @if($category === 'total')
                                        {{ $data['client_accounts']->count() + $data['technical_records']->count() + $data['product_sales']->count() + $data['cliente_no_frecuente']->count() }}
                                    @else
                                        {{ $data->count() }}
                                    @endif
                                </h4>
                                <p class="text-muted mb-0">Total de registros</p>
                            </div>
                        </div>
                        @if($category !== 'product_sales')
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success">
                                    @if($category === 'total')
                                        ${{ number_format(
                                            $data['client_accounts']->sum('amount') + 
                                            $data['technical_records']->sum('service_cost') + 
                                            $data['cliente_no_frecuente']->sum('monto'), 2
                                        ) }}
                                    @else
                                        ${{ number_format($data->sum(function($item) use ($category) {
                                            switch($category) {
                                                case 'client_accounts': return $item->amount;
                                                case 'technical_records': return $item->service_cost;
                                                case 'cliente_no_frecuente': return $item->monto;
                                                default: return 0;
                                            }
                                        }), 2) }}
                                    @endif
                                </h4>
                                <p class="text-muted mb-0">Total en dinero</p>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info">
                                    @if($category === 'total')
                                        @php
                                            $totalAmount = $data['client_accounts']->sum('amount') + 
                                                         $data['technical_records']->sum('service_cost') + 
                                                         $data['cliente_no_frecuente']->sum('monto');
                                            $totalCount = $data['client_accounts']->count() + 
                                                         $data['technical_records']->count() + 
                                                         $data['cliente_no_frecuente']->count();
                                            $average = $totalCount > 0 ? $totalAmount / $totalCount : 0;
                                        @endphp
                                        ${{ number_format($average, 2) }}
                                    @else
                                        ${{ number_format($data->avg(function($item) use ($category) {
                                            switch($category) {
                                                case 'client_accounts': return $item->amount;
                                                case 'technical_records': return $item->service_cost;
                                                case 'cliente_no_frecuente': return $item->monto;
                                                default: return 0;
                                            }
                                        }), 2) }}
                                    @endif
                                </h4>
                                <p class="text-muted mb-0">Promedio por registro</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">
                                    @if($startDate->ne($endDate))
                                        {{ $startDate->diffInDays($endDate) + 1 }} días
                                    @else
                                        1 día
                                    @endif
                                </h4>
                                <p class="text-muted mb-0">Período analizado</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
