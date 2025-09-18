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

    @if($category === 'total')
        <!-- Resumen Total -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Resumen por Categoría</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-success">Presupuestos</h6>
                                    <h4 class="text-success">${{ number_format($details['quotations']->sum('final_amount'), 2) }}</h4>
                                    <small class="text-muted">{{ $details['quotations']->count() }} registros</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-info">Fichas Técnicas</h6>
                                    <h4 class="text-info">${{ number_format($details['technical_records']->sum('final_amount'), 2) }}</h4>
                                    <small class="text-muted">{{ $details['technical_records']->count() }} registros</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-warning">Cuentas Corrientes</h6>
                                    <h4 class="text-warning">${{ number_format($details['client_accounts']->sum('amount') + $details['distributor_accounts']->sum('amount'), 2) }}</h4>
                                    <small class="text-muted">{{ $details['client_accounts']->count() + $details['distributor_accounts']->count() }} movimientos</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-primary">TOTAL</h6>
                                    <h4 class="text-primary">${{ number_format($details['quotations']->sum('final_amount') + $details['technical_records']->sum('final_amount') + $details['client_accounts']->sum('amount') + $details['distributor_accounts']->sum('amount'), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($category === 'quotations' || $category === 'total')
        <!-- Presupuestos -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar text-success"></i>
                            Presupuestos ({{ $category === 'total' ? $details['quotations']->count() : $details->count() }} registros)
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(($category === 'quotations' ? $details : $details['quotations'])->count() > 0)
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
                                        @foreach($category === 'quotations' ? $details : $details['quotations'] as $quotation)
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

    @if($category === 'current_accounts' || $category === 'total')
        <!-- Cuentas Corrientes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calculator text-warning"></i>
                            Cuentas Corrientes
                            @if($category === 'total')
                                ({{ $details['client_accounts']->count() + $details['distributor_accounts']->count() }} movimientos)
                            @else
                                ({{ $details['client_accounts']->count() + $details['distributor_accounts']->count() }} movimientos)
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $clientAccounts = $category === 'current_accounts' ? $details['client_accounts'] : $details['client_accounts'];
                            $distributorAccounts = $category === 'current_accounts' ? $details['distributor_accounts'] : $details['distributor_accounts'];
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

</div>
@endsection
