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
    <!-- Total destacado -->
    <div class="row mb-4">
        <div class="col-12 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'total', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
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
                                <h2 class="card-text mb-0">${{ number_format($periodSales['total'] ?? 0, 2) }}</h2>
                            </div>
                            <div>
                                <i class="fas fa-dollar-sign fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Primera fila: Fichas Técnicas y Cuentas Corrientes -->
    <div class="row mb-4">
        <!-- Fichas técnicas -->
        <div class="col-md-6 mb-3">
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
        <div class="col-md-6 mb-3">
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
    </div>

    <!-- Segunda fila: CC Pagas y Clientes No Frecuentes -->
    <div class="row mb-4">
        <!-- CC Pagas -->
        <div class="col-md-6 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'distributor_accounts_payments', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card" style="background-color: #6f42c1; color: white; cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">CC Pagas</h6>
                                <h3 class="card-text">${{ number_format($periodSales['distributor_accounts_payments'] ?? 0, 2) }}</h3>
                                <small>{{ $periodSales['count_distributor_accounts_payments'] ?? 0 }} pagos</small>
                            </div>
                            <div>
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Clientes No Frecuentes -->
        <div class="col-md-6 mb-3">
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

    <!-- Tercera fila: Resumen GENERAL por Forma de Pago (Todas las ventas) -->
    @php
        $startOfPeriod = $startDate->copy()->startOfDay();
        $endOfPeriod = $endDate->copy()->endOfDay();
        
        // Obtener IDs de fichas técnicas en cuenta corriente (para excluirlas)
        $technicalRecordsInCC = \App\Models\DistributorCurrentAccount::whereNotNull('distributor_technical_record_id')
            ->where('type', 'debt')
            ->join('distributor_technical_records', 'distributor_current_accounts.distributor_technical_record_id', '=', 'distributor_technical_records.id')
            ->whereBetween('distributor_technical_records.purchase_date', [$startOfPeriod, $endOfPeriod])
            ->pluck('distributor_current_accounts.distributor_technical_record_id')->toArray();
        
        // Fichas técnicas - excluyendo las que están en CC
        $fichasTecnicas = \App\Models\DistributorTechnicalRecord::whereBetween('purchase_date', [$startOfPeriod, $endOfPeriod])
            ->whereNotIn('id', $technicalRecordsInCC)
            ->get();
        
        // Clientes no frecuentes
        $clientesNoFrecuentesDist = \App\Models\DistributorClienteNoFrecuente::whereBetween('fecha', [$startOfPeriod, $endOfPeriod])->get();
        
        // Calcular totales por forma de pago - Fichas técnicas
        $ftEfectivo = $fichasTecnicas->where('payment_method', 'efectivo')->sum('final_amount');
        $ftTarjeta = $fichasTecnicas->where('payment_method', 'tarjeta')->sum('final_amount');
        $ftTransferencia = $fichasTecnicas->where('payment_method', 'transferencia')->sum('final_amount');
        $ftDeuda = $fichasTecnicas->where('payment_method', 'deuda')->sum('final_amount');
        
        $countFtEfectivo = $fichasTecnicas->where('payment_method', 'efectivo')->count();
        $countFtTarjeta = $fichasTecnicas->where('payment_method', 'tarjeta')->count();
        $countFtTransferencia = $fichasTecnicas->where('payment_method', 'transferencia')->count();
        $countFtDeuda = $fichasTecnicas->where('payment_method', 'deuda')->count();
        
        // Calcular totales por forma de pago - Clientes no frecuentes
        $cnfEfectivo = $clientesNoFrecuentesDist->where('forma_pago', 'efectivo')->sum('monto');
        $cnfTarjeta = $clientesNoFrecuentesDist->where('forma_pago', 'tarjeta')->sum('monto');
        $cnfTransferencia = $clientesNoFrecuentesDist->where('forma_pago', 'transferencia')->sum('monto');
        $cnfDeudor = $clientesNoFrecuentesDist->where('forma_pago', 'deudor')->sum('monto');
        
        $countCnfEfectivo = $clientesNoFrecuentesDist->where('forma_pago', 'efectivo')->count();
        $countCnfTarjeta = $clientesNoFrecuentesDist->where('forma_pago', 'tarjeta')->count();
        $countCnfTransferencia = $clientesNoFrecuentesDist->where('forma_pago', 'transferencia')->count();
        $countCnfDeudor = $clientesNoFrecuentesDist->where('forma_pago', 'deudor')->count();
        
        // TOTALES GENERALES
        $totalEfectivoDist = $ftEfectivo + $cnfEfectivo;
        $totalTarjetaDist = $ftTarjeta + $cnfTarjeta;
        $totalTransferenciaDist = $ftTransferencia + $cnfTransferencia;
        $totalDeudorDist = $ftDeuda + $cnfDeudor;
        
        $countEfectivoDist = $countFtEfectivo + $countCnfEfectivo;
        $countTarjetaDist = $countFtTarjeta + $countCnfTarjeta;
        $countTransferenciaDist = $countFtTransferencia + $countCnfTransferencia;
        $countDeudorDist = $countFtDeuda + $countCnfDeudor;
    @endphp
    
    <div class="row mb-4">
        <div class="col-12 mb-2">
            <h5 class="text-muted">
                <i class="fas fa-credit-card me-2"></i>Resumen General por Forma de Pago
            </h5>
            <small class="text-muted">Incluye: Fichas Técnicas + Clientes No Frecuentes</small>
        </div>
        
        <!-- Efectivo -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'forma_pago_efectivo', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card border-success" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-success">Efectivo</h6>
                                <h4 class="card-text text-success">${{ number_format($totalEfectivoDist, 2) }}</h4>
                                <small class="text-muted">{{ $countEfectivoDist }} venta(s)</small>
                            </div>
                            <div>
                                <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-success py-1">
                        <small class="text-muted">
                            Fichas: ${{ number_format($ftEfectivo, 2) }} ({{ $countFtEfectivo }}) | 
                            No Frec: ${{ number_format($cnfEfectivo, 2) }} ({{ $countCnfEfectivo }})
                        </small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tarjeta -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'forma_pago_tarjeta', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card border-primary" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-primary">Tarjeta</h6>
                                <h4 class="card-text text-primary">${{ number_format($totalTarjetaDist, 2) }}</h4>
                                <small class="text-muted">{{ $countTarjetaDist }} venta(s)</small>
                            </div>
                            <div>
                                <i class="fas fa-credit-card fa-2x text-primary"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-primary py-1">
                        <small class="text-muted">
                            Fichas: ${{ number_format($ftTarjeta, 2) }} ({{ $countFtTarjeta }}) | 
                            No Frec: ${{ number_format($cnfTarjeta, 2) }} ({{ $countCnfTarjeta }})
                        </small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Transferencia -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'forma_pago_transferencia', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card border-info" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-info">Transferencia</h6>
                                <h4 class="card-text text-info">${{ number_format($totalTransferenciaDist, 2) }}</h4>
                                <small class="text-muted">{{ $countTransferenciaDist }} venta(s)</small>
                            </div>
                            <div>
                                <i class="fas fa-university fa-2x text-info"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-info py-1">
                        <small class="text-muted">
                            Fichas: ${{ number_format($ftTransferencia, 2) }} ({{ $countFtTransferencia }}) | 
                            No Frec: ${{ number_format($cnfTransferencia, 2) }} ({{ $countCnfTransferencia }})
                        </small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Deudor -->
        <div class="col-md-3 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'forma_pago_deudor', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card border-danger" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-danger">Deudor</h6>
                                <h4 class="card-text text-danger">${{ number_format($totalDeudorDist, 2) }}</h4>
                                <small class="text-muted">{{ $countDeudorDist }} venta(s)</small>
                            </div>
                            <div>
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-danger py-1">
                        <small class="text-muted">
                            Fichas: ${{ number_format($ftDeuda, 2) }} ({{ $countFtDeuda }}) | 
                            No Frec: ${{ number_format($cnfDeudor, 2) }} ({{ $countCnfDeudor }})
                        </small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Cuarta fila: Facturas AFIP (A y B) -->
    @php
        $facturasA = \App\Models\AfipInvoice::whereBetween('invoice_date', [$startOfPeriod, $endOfPeriod])
            ->where('invoice_type', 'A')
            ->where('status', 'authorized')
            ->get();
        $facturasB = \App\Models\AfipInvoice::whereBetween('invoice_date', [$startOfPeriod, $endOfPeriod])
            ->where('invoice_type', 'B')
            ->where('status', 'authorized')
            ->get();
        
        $totalFacturasA = $facturasA->sum('total');
        $totalFacturasB = $facturasB->sum('total');
        $countFacturasA = $facturasA->count();
        $countFacturasB = $facturasB->count();
        $totalFacturas = $totalFacturasA + $totalFacturasB;
    @endphp
    
    <div class="row mb-4">
        <div class="col-12 mb-2">
            <h5 class="text-muted">
                <i class="fas fa-file-invoice me-2"></i>Facturas AFIP del Período
            </h5>
            <small class="text-muted">Solo facturas autorizadas</small>
        </div>
        
        <!-- Factura A -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'facturas_a', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card" style="border-color: #0d6efd; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title" style="color: #0d6efd;">Factura A</h6>
                                <h4 class="card-text" style="color: #0d6efd;">${{ number_format($totalFacturasA, 2) }}</h4>
                                <small class="text-muted">{{ $countFacturasA }} factura(s)</small>
                            </div>
                            <div>
                                <i class="fas fa-file-invoice fa-2x" style="color: #0d6efd;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Factura B -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('daily-sales.detail', ['category' => 'facturas_b', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
               class="text-decoration-none">
                <div class="card" style="border-color: #198754; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title" style="color: #198754;">Factura B</h6>
                                <h4 class="card-text" style="color: #198754;">${{ number_format($totalFacturasB, 2) }}</h4>
                                <small class="text-muted">{{ $countFacturasB }} factura(s)</small>
                            </div>
                            <div>
                                <i class="fas fa-file-invoice fa-2x" style="color: #198754;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Facturas -->
        <div class="col-md-4 mb-3">
            <div class="card" style="border-color: #6c757d;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-secondary">Total Facturado</h6>
                            <h4 class="card-text text-secondary">${{ number_format($totalFacturas, 2) }}</h4>
                            <small class="text-muted">{{ $countFacturasA + $countFacturasB }} factura(s)</small>
                        </div>
                        <div>
                            <i class="fas fa-file-invoice-dollar fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
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