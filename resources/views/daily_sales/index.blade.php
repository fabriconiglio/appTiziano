@extends('layouts.app')

@section('title', 'Ventas por Día')

@section('styles')
<style>
.clickable-card {
    transition: all 0.3s ease;
}

.clickable-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.clickable-card:active {
    transform: translateY(0);
}
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 text-dark mb-2">
                Ventas por Período
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
                Dashboard de ventas 
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
                    <form method="GET" action="{{ route('daily-sales.index') }}" class="row g-3">
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
                                <a href="{{ route('daily-sales.index') }}" class="btn btn-secondary">
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

    <!-- Tarjetas de resumen del período -->
    <div class="row mb-4">
        <!-- Total del período -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'total', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-primary text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">
                                    @if($startDate->ne($endDate))
                                        Total del Período
                                    @else
                                        Total del Día
                                    @endif
                                </h6>
                                <h3 class="card-text">${{ number_format($periodSales['total'] ?? 0, 2) }}</h3>
                            </div>
                            <div>
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Presupuestos -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'quotations', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-success text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Presupuestos</h6>
                                <h3 class="card-text">${{ number_format($periodSales['quotations'] ?? 0, 2) }}</h3>
                                <small>{{ $periodSales['count_quotations'] ?? 0 }} ventas</small>
                            </div>
                            <div>
                                <i class="fas fa-file-invoice-dollar fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Fichas técnicas -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'technical_records', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-info text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Fichas Técnicas</h6>
                                <h3 class="card-text">${{ number_format($periodSales['technical_records'] ?? 0, 2) }}</h3>
                                <small>{{ $periodSales['count_technical_records'] ?? 0 }} ventas</small>
                            </div>
                            <div>
                                <i class="fas fa-clipboard-list fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cuentas corrientes -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'current_accounts', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-warning text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Cuentas Corrientes</h6>
                                <h3 class="card-text">${{ number_format(($periodSales['client_accounts'] ?? 0) + ($periodSales['distributor_accounts'] ?? 0), 2) }}</h3>
                                <small>{{ ($periodSales['count_client_accounts'] ?? 0) + ($periodSales['count_distributor_accounts'] ?? 0) }} ventas</small>
                            </div>
                            <div>
                                <i class="fas fa-calculator fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Clientes No Frecuentes -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'cliente_no_frecuente', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card bg-secondary text-white" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Clientes No Frecuentes</h6>
                                <h3 class="card-text">${{ number_format($periodSales['cliente_no_frecuente'] ?? 0, 2) }}</h3>
                                <small>{{ $periodSales['count_cliente_no_frecuente'] ?? 0 }} ventas</small>
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

    @if($startDate->eq($endDate) && $yesterdaySales)
    <!-- Comparación con ayer (solo para días específicos) -->
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
                            <span class="fw-bold text-success">${{ number_format($periodSales['total'] ?? 0, 2) }}</span>
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
                        $difference = ($periodSales['total'] ?? 0) - ($yesterdaySales['total'] ?? 0);
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
    @endif

        @if($startDate->eq($endDate) && $yesterdaySales)
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
        @else
        <!-- Estadísticas del período -->
        <div class="col-lg-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estadísticas del Período</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Días consultados</h6>
                                <h4 class="text-primary">{{ $startDate->diffInDays($endDate) + 1 }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Promedio diario</h6>
                                <h4 class="text-success">${{ number_format(($periodSales['total'] ?? 0) / max(1, $startDate->diffInDays($endDate) + 1), 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h6 class="text-muted">Total del período</h6>
                                <h4 class="text-info">${{ number_format($periodSales['total'] ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>



</div>
@endsection

@section('scripts')
<script>
// Validación de fechas
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        const today = new Date().toISOString().split('T')[0];
        startDateInput.setAttribute('max', today);
        endDateInput.setAttribute('max', today);
        
        function validateDates() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            
            if (startDate && endDate && startDate > endDate) {
                endDateInput.value = startDate;
            }
            if (startDate) {
                endDateInput.setAttribute('min', startDate);
            }
        }
        
        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
        validateDates();
    }
});
</script>
@endsection 