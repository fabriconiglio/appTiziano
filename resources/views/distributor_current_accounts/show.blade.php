@extends('layouts.app')

@section('title', 'Cuenta Corriente - ' . $distributorClient->full_name)

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Cuenta Corriente: {{ $distributorClient->full_name }}</h5>
            <div>
                <a href="{{ route('distributor-clients.current-accounts.export-pdf', $distributorClient) }}" class="btn btn-danger btn-sm me-2" target="_blank">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </a>
                <a href="{{ route('distributor-clients.current-accounts.create', $distributorClient) }}" class="btn btn-success btn-sm me-2">
                    <i class="fas fa-plus"></i> Nuevo Movimiento
                </a>
                <a href="{{ route('distributor-current-accounts.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>

        <div class="card-body">
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

            <!-- Información del Cliente -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Información del Distribuidor</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Nombre Completo:</dt>
                        <dd class="col-sm-8">{{ $distributorClient->full_name }}</dd>

                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $distributorClient->email ?? 'No registrado' }}</dd>

                        <dt class="col-sm-4">Teléfono:</dt>
                        <dd class="col-sm-8">{{ $distributorClient->phone ?? 'No registrado' }}</dd>

                        <dt class="col-sm-4">DNI:</dt>
                        <dd class="col-sm-8">{{ $distributorClient->dni ?? 'No registrado' }}</dd>
                    </dl>
                </div>

                <div class="col-md-6">
                    <h6 class="border-bottom pb-2 mb-3">Estado de la Cuenta</h6>
                    <div class="text-center">
                        <h3 class="mb-2 {{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-dark') }}">
                            ${{ $formattedBalance }}
                        </h3>
                        @if($currentBalance > 0)
                            <span class="badge bg-danger fs-6">Con Deuda</span>
                        @elseif($currentBalance < 0)
                            <span class="badge bg-success fs-6">A Favor</span>
                        @else
                            <span class="badge bg-secondary fs-6">Al Día</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Registros Técnicos Pendientes -->
            @if($technicalRecords->count() > 0)
                <div class="card mb-4 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Registros Técnicos Pendientes
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Los siguientes registros técnicos no tienen movimientos de cuenta corriente asociados:
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-warning">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Monto Total</th>
                                        <th>Pago Adelantado</th>
                                        <th>Deuda</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($technicalRecords as $record)
                                        <tr>
                                            <td>{{ $record->purchase_date->format('d/m/Y') }}</td>
                                            <td>${{ number_format($record->final_amount, 2, ',', '.') }}</td>
                                            <td>${{ number_format($record->advance_payment ?? 0, 2, ',', '.') }}</td>
                                            <td>${{ number_format($record->final_amount, 2, ',', '.') }}</td>
                                            <td>
                                                <form action="{{ route('distributor-clients.current-accounts.create-from-technical-record', [$distributorClient, $record]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-plus"></i> Crear Deuda
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Movimientos de Cuenta Corriente -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Movimientos de Cuenta Corriente</h6>
                </div>
                <div class="card-body">
                    @if($currentAccounts->count() > 0)
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
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentAccounts as $account)
                                        <tr>
                                            <td>{{ $account->date->format('d/m/Y') }}</td>
                                            <td>
                                                @if($account->type === 'debt')
                                                    <span class="badge bg-danger">Deuda</span>
                                                @else
                                                    <span class="badge bg-success">Pago</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $account->description }}
                                                @if($account->observations)
                                                    <br><small class="text-muted">{{ $account->observations }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $account->reference ?? '-' }}</td>
                                            <td>
                                                <span class="fw-bold {{ $account->type === 'debt' ? 'text-danger' : 'text-success' }}">
                                                    {{ $account->type === 'debt' ? '-' : '+' }}${{ number_format($account->amount, 2, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>{{ $account->user->name }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('distributor-clients.current-accounts.edit', [$distributorClient, $account]) }}" 
                                                       class="btn btn-warning btn-sm"
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('distributor-clients.current-accounts.destroy', [$distributorClient, $account]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-danger btn-sm"
                                                                title="Eliminar"
                                                                onclick="return confirm('¿Estás seguro de que quieres eliminar este movimiento?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No hay movimientos registrados en la cuenta corriente.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 