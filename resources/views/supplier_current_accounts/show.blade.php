@extends('layouts.app')

@section('title', 'Cuenta Corriente - ' . $supplier->full_name)

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-invoice-dollar"></i> Cuenta Corriente</h1>
        <div>
            <a href="{{ route('suppliers.create-payment', $supplier) }}" class="btn btn-success me-2">
                <i class="fas fa-money-bill-wave"></i> Registrar Pago
            </a>
            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Proveedor
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Información del Proveedor -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">{{ $supplier->full_name }}</h5>
        </div>
        <div class="card-body py-2">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">CUIT:</small>
                    <span class="fw-bold">{{ $supplier->cuit ?? 'No registrado' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Teléfono:</small>
                    <span class="fw-bold">{{ $supplier->phone ?? 'No registrado' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Dirección:</small>
                    <span class="fw-bold">{{ $supplier->address ?? 'No registrada' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Cuenta -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center py-2">
                    <small class="text-muted text-uppercase">Importes Facturas</small>
                    <h5 class="text-primary mb-0">${{ number_format($totalDebts, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center py-2">
                    <small class="text-muted text-uppercase">Total Pagos</small>
                    <h5 class="text-success mb-0">${{ number_format($totalPayments, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center py-2">
                    <small class="text-muted text-uppercase">Excedente</small>
                    <h5 class="text-info mb-0">${{ number_format($totalCredits, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card {{ $currentBalance > 0 ? 'border-danger' : ($currentBalance < 0 ? 'border-success' : 'border-dark') }}">
                <div class="card-body text-center py-2">
                    <small class="text-muted text-uppercase">Saldo Final</small>
                    <h5 class="{{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-dark') }} mb-0">
                        {{ $formattedBalance }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Fórmula -->
    <div class="alert {{ $currentBalance > 0 ? 'alert-danger' : ($currentBalance < 0 ? 'alert-success' : 'alert-secondary') }} py-2 mb-4">
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-2">
            <span><strong>Importes Facturas</strong> (${{ number_format($totalDebts, 2, ',', '.') }})</span>
            <span>-</span>
            <span><strong>Pagos</strong> (${{ number_format($totalPayments, 2, ',', '.') }})</span>
            <span>-</span>
            <span><strong>Excedente</strong> (${{ number_format($totalCredits, 2, ',', '.') }})</span>
            <span>=</span>
            <span class="fw-bold fs-5">
                @if($currentBalance > 0)
                    ${{ number_format($currentBalance, 2, ',', '.') }} <span class="badge bg-danger">DEUDA</span>
                @elseif($currentBalance < 0)
                    ${{ number_format(abs($currentBalance), 2, ',', '.') }} <span class="badge bg-success">EXCEDENTE A FAVOR</span>
                @else
                    $0,00 <span class="badge bg-secondary">AL DÍA</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Historial de Movimientos -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list"></i> Historial de Movimientos</h5>
            <small class="text-muted">Ordenado cronológicamente</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">Fecha Mov.</th>
                            <th>N° Factura</th>
                            <th>Descripción</th>
                            <th class="text-end">Débito</th>
                            <th class="text-end">Crédito</th>
                            <th class="text-end px-3">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currentAccounts as $account)
                            <tr>
                                <td class="px-3">{{ $account->date->format('d/m/Y') }}</td>
                                <td>
                                    @if($account->supplierPurchase)
                                        <span class="badge bg-primary">{{ $account->supplierPurchase->receipt_number }}</span>
                                    @else
                                        <span class="text-muted">{{ $account->reference ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $account->description }}</small>
                                    @if($account->observations)
                                        <br><small class="text-muted">{{ $account->observations }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($account->type === 'debt')
                                        <span class="text-danger fw-bold">${{ number_format($account->amount, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($account->type === 'payment' || $account->type === 'credit')
                                        <span class="text-success fw-bold">${{ number_format($account->amount, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="text-end px-3">
                                    @php $rb = $account->running_balance; @endphp
                                    <span class="fw-bold {{ $rb > 0 ? 'text-danger' : ($rb < 0 ? 'text-success' : 'text-dark') }}">
                                        @if($rb < 0)
                                            -${{ number_format(abs($rb), 2, ',', '.') }}
                                        @else
                                            ${{ number_format($rb, 2, ',', '.') }}
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-file-invoice fa-3x text-muted mb-3 d-block"></i>
                                    No hay movimientos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($currentAccounts->count() > 0)
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td class="px-3" colspan="3"><strong>TOTALES</strong></td>
                                <td class="text-end text-danger">${{ number_format($totalDebts, 2, ',', '.') }}</td>
                                <td class="text-end text-success">${{ number_format($totalPayments + $totalCredits, 2, ',', '.') }}</td>
                                <td class="text-end px-3">
                                    <span class="{{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-dark') }}">
                                        @if($currentBalance > 0)
                                            ${{ number_format($currentBalance, 2, ',', '.') }}
                                            <small class="badge bg-danger">Debe</small>
                                        @elseif($currentBalance < 0)
                                            -${{ number_format(abs($currentBalance), 2, ',', '.') }}
                                            <small class="badge bg-success">Excedente</small>
                                        @else
                                            $0,00
                                            <small class="badge bg-secondary">Al día</small>
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
