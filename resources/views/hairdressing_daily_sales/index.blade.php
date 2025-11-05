@extends('layouts.app')

@section('title', 'Ventas por Día - Peluquería')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 text-dark mb-2">
                Ventas por Período - Peluquería
                @if($startDate->ne($endDate))
                    - {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}
                @else
                    - {{ $startDate->format('d/m/Y') }}
                @endif
                @if($startDate->ne($today) || $endDate->ne($today))
                    <span class="badge bg-info ms-2">Período Histórico</span>
                @endif
            </h1>
            <p class="text-muted">
                Dashboard de ventas de peluquería
                @if($startDate->ne($endDate))
                    del período {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}
                @else
                    @if($startDate->ne($today))
                        para el {{ $startDate->format('d/m/Y') }}
                    @else
                        que se actualiza automáticamente cada día
                    @endif
                @endif
            </p>
        </div>
    </div>

    <!-- Filtro de fechas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Seleccionar Período</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hairdressing-daily-sales.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Fecha de Inicio:</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ request('start_date', $startDate->format('Y-m-d')) }}" 
                                   max="{{ $today->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">Fecha de Fin:</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ request('end_date', $endDate->format('Y-m-d')) }}" 
                                   max="{{ $today->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Consultar
                                </button>
                                <a href="{{ route('hairdressing-daily-sales.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-calendar-day"></i> Hoy
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Información:</label>
                            <div class="text-muted small">
                                @if($startDate->ne($today) || $endDate->ne($today))
                                    <i class="fas fa-info-circle"></i> Mostrando datos históricos
                                @else
                                    <i class="fas fa-clock"></i> Datos en tiempo real
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de resumen del día -->
    <!-- Total destacado -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <a href="{{ route('hairdressing-daily-sales.detail', ['category' => 'total', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-primary text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-2">
                                    @if($startDate->ne($endDate))
                                        Total del Período
                                    @else
                                        Total del Día
                                    @endif
                                </h5>
                                <h2 class="card-text mb-0">${{ number_format($todaySales['total'] ?? 0, 2) }}</h2>
                            </div>
                            <div>
                                <i class="fas fa-cut fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Primera fila: Cuentas Corrientes -->
    <div class="row mb-4">
        <!-- Cuentas corrientes -->
        <div class="col-md-6 mb-3">
            <a href="{{ route('hairdressing-daily-sales.detail', ['category' => 'client_accounts', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-success text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Cuentas Corrientes</h6>
                                <h3 class="card-text">${{ number_format($todaySales['client_accounts'] ?? 0, 2) }}</h3>
                                <small>{{ $todaySales['count_client_accounts'] ?? 0 }} ventas</small>
                            </div>
                            <div>
                                <i class="fas fa-calculator fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- CC Pagas -->
        <div class="col-md-6 mb-3">
            <a href="{{ route('hairdressing-daily-sales.detail', ['category' => 'client_accounts_payments', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card" style="background-color: #6f42c1; color: white; cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">CC Pagas</h6>
                                <h3 class="card-text">${{ number_format($todaySales['client_accounts_payments'] ?? 0, 2) }}</h3>
                                <small>{{ $todaySales['count_client_accounts_payments'] ?? 0 }} pagos</small>
                            </div>
                            <div>
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Segunda fila: Servicios, Productos y Clientes No Frecuentes -->
    <div class="row mb-4">
        <!-- Servicios -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('hairdressing-daily-sales.detail', ['category' => 'technical_records', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-info text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Servicios</h6>
                                <h3 class="card-text">${{ number_format($todaySales['technical_records'] ?? 0, 2) }}</h3>
                                <small>{{ $todaySales['count_technical_records'] ?? 0 }} servicios</small>
                            </div>
                            <div>
                                <i class="fas fa-scissors fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Productos -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('hairdressing-daily-sales.detail', ['category' => 'product_sales', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-warning text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Productos</h6>
                                <h3 class="card-text">${{ number_format($todaySales['product_sales'] ?? 0, 2) }}</h3>
                                <small>{{ $todaySales['count_product_sales'] ?? 0 }} ventas</small>
                            </div>
                            <div>
                                <i class="fas fa-box fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Clientes No Frecuentes -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('hairdressing-daily-sales.detail', ['category' => 'cliente_no_frecuente', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-secondary text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Clientes No Frecuentes</h6>
                                <h3 class="card-text">${{ number_format($todaySales['cliente_no_frecuente_sales'] ?? 0, 2) }}</h3>
                                <small>{{ $todaySales['count_cliente_no_frecuente'] ?? 0 }} clientes</small>
                            </div>
                            <div>
                                <i class="fas fa-user-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Comparación con ayer -->
    <div class="row mb-4">
        <!-- Comparación del día -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Comparación con Ayer</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">
                            <span class="text-muted">Hoy:</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="fw-bold text-success">${{ number_format($todaySales['total'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <span class="text-muted">Ayer:</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="fw-bold text-dark">${{ number_format($yesterdaySales['total'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <hr>
                    @php
                        $difference = ($todaySales['total'] ?? 0) - ($yesterdaySales['total'] ?? 0);
                        $percentage = ($yesterdaySales['total'] ?? 0) > 0 ? ($difference / ($yesterdaySales['total'] ?? 1)) * 100 : 0;
                    @endphp
                    <div class="row">
                        <div class="col-6">
                            <span class="text-muted">Diferencia:</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="fw-bold {{ $difference >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $difference >= 0 ? '+' : '' }}${{ number_format($difference, 2) }}
                                ({{ $difference >= 0 ? '+' : '' }}{{ number_format($percentage, 1) }}%)
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas del mes -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estadísticas del Mes</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">
                            <span class="text-muted">Total del mes:</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="fw-bold text-primary">${{ number_format($monthlyStats['total'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <span class="text-muted">Promedio diario:</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="fw-bold text-dark">${{ number_format(($monthlyStats['total'] ?? 0) / max(1, $startDate->day), 2) }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <span class="text-muted">Proyección mensual:</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="fw-bold text-info">${{ number_format((($monthlyStats['total'] ?? 0) / max(1, $startDate->day)) * $startDate->daysInMonth, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Servicios y productos más populares -->
    <div class="row mb-4">
        <!-- Servicios más populares -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-star text-warning me-2"></i>
                        Servicios Más Populares del Día
                    </h5>
                </div>
                <div class="card-body">
                    @if($popularServices->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($popularServices as $service)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $service->hair_treatments ?? 'Servicio General' }}</h6>
                                        <small class="text-muted">{{ $service->service_type ?? 'Tratamiento de cabello' }}</small>
                                        <br>
                                        <small class="text-success">${{ number_format($service->total_cost, 2) }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary rounded-pill">{{ $service->total }}</span>
                                        <br>
                                        <small class="text-muted">servicios</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">No hay servicios registrados para este día</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Productos más vendidos -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-trophy text-success me-2"></i>
                        Productos Más Vendidos del Día
                    </h5>
                </div>
                <div class="card-body">
                    @if($popularProducts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($popularProducts as $product)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $product->name }}</h6>
                                        <small class="text-muted">{{ $product->description }}</small>
                                        <br>
                                        <small class="text-success">${{ number_format($product->total_amount, 2) }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success rounded-pill">{{ $product->total_quantity }}</span>
                                        <br>
                                        <small class="text-muted">unidades</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">No hay productos vendidos para este día</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection 