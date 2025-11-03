@extends('layouts.app')

@section('title', 'Cuenta Corriente - ' . $hairdressingSupplier->full_name)

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Cuenta Corriente - {{ $hairdressingSupplier->full_name }}</h1>
        <a href="{{ route('hairdressing-suppliers.show', $hairdressingSupplier) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Proveedor
        </a>
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
        <div class="card-header">
            <h5 class="mb-0">Información del Proveedor</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nombre:</strong> {{ $hairdressingSupplier->full_name }}</p>
                    <p><strong>CUIT:</strong> {{ $hairdressingSupplier->cuit ?? 'No registrado' }}</p>
                    <p><strong>Email:</strong> {{ $hairdressingSupplier->email ?? 'No registrado' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Teléfono:</strong> {{ $hairdressingSupplier->phone ?? 'No registrado' }}</p>
                    <p><strong>Dirección:</strong> {{ $hairdressingSupplier->address ?? 'No registrada' }}</p>
                    <p><strong>Estado:</strong> 
                        <span class="badge {{ $hairdressingSupplier->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $hairdressingSupplier->status_text }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Saldo Actual -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Saldo Actual</h5>
        </div>
        <div class="card-body text-center">
            <h2 class="{{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-dark') }}">
                {{ $formattedBalance }}
            </h2>
            @if($currentBalance > 0)
                <p class="text-muted">El proveedor tiene deuda pendiente</p>
            @elseif($currentBalance < 0)
                <p class="text-muted">Tienes saldo a favor con este proveedor</p>
            @else
                <p class="text-muted">Cuenta al día</p>
            @endif
        </div>
    </div>

    <!-- Historial de Movimientos -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Historial de Movimientos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Referencia</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currentAccounts as $account)
                            <tr>
                                <td>{{ $account->date->format('d/m/Y') }}</td>
                                <td>
                                    @switch($account->type)
                                        @case('debt')
                                            <span class="badge bg-danger">Deuda</span>
                                            @break
                                        @case('payment')
                                            <span class="badge bg-success">Pago</span>
                                            @break
                                        @case('credit')
                                            <span class="badge bg-info">Crédito</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $account->description }}</td>
                                <td>{{ $account->reference ?? '-' }}</td>
                                <td>
                                    <span class="fw-bold {{ $account->type == 'debt' ? 'text-danger' : ($account->type == 'payment' ? 'text-success' : 'text-info') }}">
                                        ${{ number_format($account->amount, 2) }}
                                    </span>
                                </td>
                                <td>{{ $account->user->name }}</td>
                                <td>{{ $account->observations ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No hay movimientos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
