@extends('layouts.app')

@section('title', 'Detalle de Ventas')

@section('content')
<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 text-dark mb-2">
                        @switch($category)
                            @case('total')
                                Resumen Total
                                @break
                            @case('quotations')
                                Presupuestos
                                @break
                            @case('technical_records')
                                Fichas Técnicas
                                @break
                            @case('current_accounts')
                                Cuentas Corrientes
                                @break
                            @case('distributor_accounts_payments')
                                CC Pagas
                                @break
                            @case('cliente_no_frecuente')
                                Clientes No Frecuentes
                                @break
                            @case('forma_pago_efectivo')
                                Pagos en Efectivo
                                @break
                            @case('forma_pago_tarjeta')
                                Pagos con Tarjeta
                                @break
                            @case('forma_pago_transferencia')
                                Pagos por Transferencia
                                @break
                            @case('forma_pago_deudor')
                                Ventas a Deudores
                                @break
                            @case('facturas_a')
                                Facturas A
                                @break
                            @case('facturas_b')
                                Facturas B
                                @break
                        @endswitch
                    </h1>
                    <p class="text-muted">
                        @if($startDate->ne($endDate))
                            Período: {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}
                        @else
                            Fecha: {{ $startDate->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('daily-sales.index', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($category === 'facturas_a' || $category === 'facturas_b')
        <!-- Vista especial para facturas AFIP -->
        @php
            $tipoFactura = $category === 'facturas_a' ? 'A' : 'B';
            $colorFactura = $category === 'facturas_a' ? '#0d6efd' : '#198754';
        @endphp
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card" style="border-color: {{ $colorFactura }};">
                    <div class="card-header" style="background-color: {{ $colorFactura }}; color: white;">
                        <h5 class="card-title mb-0" style="color:black;">
                            <i class="fas fa-file-invoice me-2" style="color:black;"></i>Facturas {{ $tipoFactura }} ({{ $details->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h6 class="text-muted">Total Facturado</h6>
                                    <h3 style="color: {{ $colorFactura }};">${{ number_format($details->sum('total'), 2) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h6 class="text-muted">Cantidad de Facturas</h6>
                                    <h3 style="color: {{ $colorFactura }};">{{ $details->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        
                        @if($details->count() > 0)
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-striped table-hover">
                                    <thead class="sticky-top table-dark">
                                        <tr>
                                            <th>Número</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Subtotal</th>
                                            <th>IVA</th>
                                            <th>Total</th>
                                            <th>CAE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($details as $factura)
                                        <tr>
                                            <td>
                                                <strong>{{ $factura->formatted_number }}</strong>
                                            </td>
                                            <td>
                                                {{ $factura->client_full_name }}
                                            </td>
                                            <td>{{ $factura->invoice_date->format('d/m/Y') }}</td>
                                            <td>${{ number_format($factura->subtotal, 2) }}</td>
                                            <td>${{ number_format($factura->tax_amount, 2) }}</td>
                                            <td>
                                                <strong style="color: {{ $colorFactura }};">${{ number_format($factura->total, 2) }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $factura->cae }}</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-file-invoice fa-4x text-muted mb-3"></i>
                                <h5>No hay facturas {{ $tipoFactura }}</h5>
                                <p class="text-muted mb-0">No se encontraron facturas tipo {{ $tipoFactura }} autorizadas en el período seleccionado</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @elseif(str_starts_with($category, 'forma_pago_'))
        <!-- Vista especial para forma de pago -->
        @php
            $formaPago = $details['forma_pago'];
            $badgeClass = match($formaPago) {
                'efectivo' => 'bg-success',
                'tarjeta' => 'bg-primary',
                'transferencia' => 'bg-info',
                'deudor' => 'bg-danger',
                default => 'bg-secondary'
            };
            $totalFichas = $details['technical_records']->sum('final_amount');
            $totalNoFrec = $details['cliente_no_frecuente']->sum('monto');
            $totalGeneral = $totalFichas + $totalNoFrec;
        @endphp
        
        <!-- Resumen -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            @switch($formaPago)
                                @case('efectivo')
                                    <i class="fas fa-money-bill-wave text-success me-2"></i>Pagos en Efectivo
                                    @break
                                @case('tarjeta')
                                    <i class="fas fa-credit-card text-primary me-2"></i>Pagos con Tarjeta
                                    @break
                                @case('transferencia')
                                    <i class="fas fa-university text-info me-2"></i>Pagos por Transferencia
                                    @break
                                @case('deudor')
                                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Ventas a Deudores
                                    @break
                            @endswitch
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-muted">Total General</h6>
                                    <h3 class="{{ match($formaPago) { 'efectivo' => 'text-success', 'tarjeta' => 'text-primary', 'transferencia' => 'text-info', 'deudor' => 'text-danger', default => 'text-secondary' } }}">${{ number_format($totalGeneral, 2) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-muted">Fichas Técnicas</h6>
                                    <h4 class="text-info">${{ number_format($totalFichas, 2) }}</h4>
                                    <small class="text-muted">{{ $details['technical_records']->count() }} fichas</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-muted">No Frecuentes</h6>
                                    <h4 class="text-secondary">${{ number_format($totalNoFrec, 2) }}</h4>
                                    <small class="text-muted">{{ $details['cliente_no_frecuente']->count() }} clientes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fichas Técnicas -->
        @if($details['technical_records']->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clipboard-list text-info me-2"></i>Fichas Técnicas ({{ $details['technical_records']->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($details['technical_records'] as $record)
                                    <tr>
                                        <td>#{{ $record->id }}</td>
                                        <td>{{ $record->distributorClient->name ?? 'N/A' }}</td>
                                        <td>{{ $record->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $record->purchase_type ?: 'Compra' }}</span>
                                        </td>
                                        <td class="text-info fw-bold">${{ number_format($record->final_amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Clientes No Frecuentes -->
        @if($details['cliente_no_frecuente']->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-clock text-secondary me-2"></i>Clientes No Frecuentes ({{ $details['cliente_no_frecuente']->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Teléfono</th>
                                        <th>Fecha</th>
                                        <th>Productos</th>
                                        <th>Monto</th>
                                        <th>Registrado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($details['cliente_no_frecuente'] as $cliente)
                                    <tr>
                                        <td>
                                            @if($cliente->nombre)
                                                <strong>{{ $cliente->nombre }}</strong>
                                            @else
                                                <span class="text-muted">Sin nombre</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($cliente->telefono)
                                                <span class="text-primary">{{ $cliente->telefono }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $cliente->fecha->format('d/m/Y') }}</td>
                                        <td>
                                            @if($cliente->productos)
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                                      title="{{ $cliente->productos }}">
                                                    {{ Str::limit($cliente->productos, 30) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-success fw-bold">
                                            ${{ number_format($cliente->monto, 2) }}
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $cliente->user->name }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($details['technical_records']->count() == 0 && $details['cliente_no_frecuente']->count() == 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h5>No hay registros para mostrar</h5>
                        <p class="text-muted mb-0">No se encontraron ventas con esta forma de pago en el período seleccionado</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

    @elseif($category === 'total')
        <!-- Resumen Total -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Resumen por Categoría</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="text-center">
                                    <h6 class="text-info">Fichas Técnicas</h6>
                                    <h4 class="text-info">${{ number_format($details['technical_records']->sum('final_amount'), 2) }}</h4>
                                    <small class="text-muted">{{ $details['technical_records']->count() }} registros</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center">
                                    <h6 class="mb-3" style="color: #6f42c1;">CC Pagas</h6>
                                    <h4 style="color: #6f42c1;">${{ number_format($details['distributor_accounts_payments']->sum('amount') ?? 0, 2) }}</h4>
                                    <small class="text-muted">{{ $details['distributor_accounts_payments']->count() ?? 0 }} pagos</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center">
                                    <h6 class="text-secondary">Clientes No Frecuentes</h6>
                                    <h4 class="text-secondary">${{ number_format($details['cliente_no_frecuente']->sum('monto') ?? 0, 2) }}</h4>
                                    <small class="text-muted">{{ $details['cliente_no_frecuente']->count() ?? 0 }} ventas</small>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="text-center">
                                    <h6 class="text-primary">TOTAL</h6>
                                    <h4 class="text-primary">${{ number_format($details['technical_records']->sum('final_amount') + ($details['distributor_accounts_payments']->sum('amount') ?? 0) + ($details['cliente_no_frecuente']->sum('monto') ?? 0), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($category === 'quotations')
        <!-- Presupuestos -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar text-success"></i>
                            Presupuestos ({{ $details->count() }} registros)
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($details->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($details as $quotation)
                                        <tr>
                                            <td>#{{ $quotation->id }}</td>
                                            <td>{{ $quotation->distributorClient->name ?? 'N/A' }}</td>
                                            <td>{{ $quotation->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-success fw-bold">${{ number_format($quotation->final_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-success">Activo</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay presupuestos en este período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($category === 'technical_records' || $category === 'total')
        <!-- Fichas Técnicas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clipboard-list text-info"></i>
                            Fichas Técnicas ({{ $category === 'total' ? $details['technical_records']->count() : $details->count() }} registros)
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(($category === 'technical_records' ? $details : $details['technical_records'])->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($category === 'technical_records' ? $details : $details['technical_records'] as $record)
                                        <tr>
                                            <td>#{{ $record->id }}</td>
                                            <td>{{ $record->distributorClient->name ?? 'N/A' }}</td>
                                            <td>{{ $record->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $record->purchase_type ?: 'Compra' }}</span>
                                            </td>
                                            <td class="text-info fw-bold">${{ number_format($record->final_amount, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay fichas técnicas en este período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($category === 'distributor_accounts_payments' || $category === 'total')
        <!-- CC Pagas -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-money-bill-wave" style="color: #6f42c1;"></i>
                            CC Pagas
                            @if($category === 'total')
                                ({{ $details['distributor_accounts_payments']->count() ?? 0 }} pagos)
                            @else
                                ({{ $details->count() }} pagos)
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $payments = $category === 'distributor_accounts_payments' ? $details : ($details['distributor_accounts_payments'] ?? collect());
                        @endphp

                        @if($payments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Descripción</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payments as $payment)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $payment->distributorClient->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $payment->distributorClient->email ?? '' }}</small>
                                            </td>
                                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $payment->description ?? 'Sin descripción' }}</td>
                                            <td>
                                                <span class="fw-bold" style="color: #6f42c1;">${{ number_format($payment->amount, 2) }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay pagos de cuentas corrientes en este período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($category === 'current_accounts')
        <!-- Cuentas Corrientes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calculator text-warning"></i>
                            Cuentas Corrientes
                            ({{ $details['client_accounts']->count() + $details['distributor_accounts']->count() }} movimientos)
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $clientAccounts = $details['client_accounts'];
                            $distributorAccounts = $details['distributor_accounts'];
                        @endphp

                        @if($clientAccounts->count() > 0 || $distributorAccounts->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Descripción</th>
                                            <th>Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($clientAccounts as $account)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">Cliente</span>
                                            </td>
                                            <td>{{ $account->client->name ?? 'N/A' }}</td>
                                            <td>{{ $account->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $account->description }}</td>
                                            <td class="{{ $account->type === 'debt' ? 'text-danger' : 'text-success' }} fw-bold">
                                                {{ $account->type === 'debt' ? '-' : '+' }}${{ number_format($account->amount, 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                        
                                        @foreach($distributorAccounts as $account)
                                        <tr>
                                            <td>
                                                <span class="badge bg-warning">Distribuidor</span>
                                            </td>
                                            <td>{{ $account->distributorClient->name ?? 'N/A' }}</td>
                                            <td>{{ $account->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ $account->description }}</td>
                                            <td class="{{ $account->type === 'debt' ? 'text-danger' : 'text-success' }} fw-bold">
                                                {{ $account->type === 'debt' ? '-' : '+' }}${{ number_format($account->amount, 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay movimientos de cuenta corriente en este período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($category === 'cliente_no_frecuente' || $category === 'total')
        <!-- Clientes No Frecuentes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-clock text-secondary"></i>
                            Clientes No Frecuentes
                            @if($category === 'total')
                                ({{ $details['cliente_no_frecuente']->count() }} ventas)
                            @else
                                ({{ $details->count() }} ventas)
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $clienteNoFrecuente = $category === 'cliente_no_frecuente' ? $details : $details['cliente_no_frecuente'];
                        @endphp

                        @if($clienteNoFrecuente->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Teléfono</th>
                                            <th>Fecha</th>
                                            <th>Productos</th>
                                            <th>Monto</th>
                                            <th>Forma de Pago</th>
                                            <th>Registrado por</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($clienteNoFrecuente as $cliente)
                                        <tr>
                                            <td>
                                                @if($cliente->nombre)
                                                    <strong>{{ $cliente->nombre }}</strong>
                                                @else
                                                    <span class="text-muted">Sin nombre</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cliente->telefono)
                                                    <span class="text-primary">{{ $cliente->telefono }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $cliente->fecha->format('d/m/Y') }}</td>
                                            <td>
                                                @if($cliente->productos)
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                                          title="{{ $cliente->productos }}">
                                                        {{ Str::limit($cliente->productos, 30) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-success fw-bold">
                                                ${{ number_format($cliente->monto, 2) }}
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match($cliente->forma_pago) {
                                                        'efectivo' => 'bg-success',
                                                        'tarjeta' => 'bg-primary',
                                                        'transferencia' => 'bg-info',
                                                        'deudor' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ \App\Models\DistributorClienteNoFrecuente::FORMAS_PAGO[$cliente->forma_pago] ?? 'Sin definir' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $cliente->user->name }}</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay clientes no frecuentes registrados en este período</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection




