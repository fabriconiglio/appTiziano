@extends('layouts.app')

@section('title', 'Ventas por Día')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 text-dark mb-2">
                Ventas por Día - {{ $selectedDate->format('d/m/Y') }}
                @if($selectedDate->ne($today))
                    <span class="badge bg-info ms-2">Fecha Histórica</span>
                @endif
            </h1>
            <p class="text-muted">
                Dashboard de ventas diarias 
                @if($selectedDate->ne($today))
                    para el {{ $selectedDate->format('d/m/Y') }}
                @else
                    que se actualiza automáticamente cada día
                @endif
            </p>
        </div>
    </div>
    
    <!-- Filtro de fecha -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Seleccionar Fecha</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('daily-sales.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="selected_date" class="form-label">Fecha a consultar:</label>
                            <input type="date" class="form-control" id="selected_date" name="selected_date" 
                                   value="{{ request('selected_date', $selectedDate->format('Y-m-d')) }}" 
                                   max="{{ $today->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Consultar
                                </button>
                                <a href="{{ route('daily-sales.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-calendar-day"></i> Hoy
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Información:</label>
                            <div class="text-muted small">
                                @if($selectedDate->ne($today))
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
    <div class="row mb-4">
        <!-- Total del día -->
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total del Día</h6>
                            <h3 class="card-text">${{ number_format($todaySales['total'] ?? 0, 2) }}</h3>
                        </div>
                        <div>
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Presupuestos -->
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Presupuestos</h6>
                            <h3 class="card-text">${{ number_format($todaySales['quotations'] ?? 0, 2) }}</h3>
                            <small>{{ $todaySales['count_quotations'] ?? 0 }} ventas</small>
                        </div>
                        <div>
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fichas técnicas -->
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Fichas Técnicas</h6>
                            <h3 class="card-text">${{ number_format($todaySales['technical_records'] ?? 0, 2) }}</h3>
                            <small>{{ $todaySales['count_technical_records'] ?? 0 }} ventas</small>
                        </div>
                        <div>
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cuentas corrientes -->
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Cuentas Corrientes</h6>
                            <h3 class="card-text">${{ number_format(($todaySales['client_accounts'] ?? 0) + ($todaySales['distributor_accounts'] ?? 0), 2) }}</h3>
                            <small>{{ ($todaySales['count_client_accounts'] ?? 0) + ($todaySales['count_distributor_accounts'] ?? 0) }} ventas</small>
                        </div>
                        <div>
                            <i class="fas fa-calculator fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
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
                            <span class="fw-bold text-dark">${{ number_format(($monthlyStats['total'] ?? 0) / max(1, $today->day), 2) }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <span class="text-muted">Proyección mensual:</span>
                        </div>
                        <div class="col-6 text-end">
                            <span class="fw-bold text-info">${{ number_format((($monthlyStats['total'] ?? 0) / max(1, $today->day)) * $today->daysInMonth, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection 