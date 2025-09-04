@extends('layouts.app')

@section('title', 'Cuenta Corriente - ' . $client->full_name)

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Cuenta Corriente - {{ $client->full_name }}</h1>
            <p class="text-muted mb-0">DNI: {{ $client->dni }} | Teléfono: {{ $client->phone ?? 'No registrado' }}</p>
        </div>
        <div>
            <a href="{{ route('client-current-accounts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Cuentas Corrientes
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

    <!-- Información del cliente -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Información del Cliente</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nombre:</strong></td>
                            <td>{{ $client->full_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>DNI:</strong></td>
                            <td>{{ $client->dni }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $client->email ?? 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Teléfono:</strong></td>
                            <td>{{ $client->phone ?? 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Domicilio:</strong></td>
                            <td>{{ $client->domicilio ?? 'No registrado' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Resumen de Cuenta Corriente</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="{{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-dark') }}">
                            {{ $formattedBalance }}
                        </h3>
                        <p class="text-muted">
                            @if($currentBalance > 0)
                                <i class="fas fa-exclamation-triangle text-warning"></i> El cliente tiene deuda pendiente
                            @elseif($currentBalance < 0)
                                <i class="fas fa-check-circle text-success"></i> El cliente tiene crédito a favor
                            @else
                                <i class="fas fa-check text-secondary"></i> La cuenta está al día
                            @endif
                        </p>
                        <div class="mt-3">
                            <a href="{{ route('clients.current-accounts.create', $client) }}" class="btn btn-success">
                                <i class="fas fa-plus"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Movimientos de cuenta corriente -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Movimientos de Cuenta Corriente</h5>
            <span class="badge bg-primary">{{ $currentAccounts->count() }} movimientos</span>
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
                                <th>Monto</th>
                                <th>Referencia</th>
                                <th>Creado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($currentAccounts as $movement)
                                <tr>
                                    <td>{{ $movement->date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($movement->type == 'debt')
                                            <span class="badge bg-danger">Deuda</span>
                                        @else
                                            <span class="badge bg-success">Pago</span>
                                        @endif
                                    </td>
                                    <td>{{ $movement->description }}</td>
                                    <td>
                                        <span class="fw-bold {{ $movement->type == 'debt' ? 'text-danger' : 'text-success' }}">
                                            ${{ number_format($movement->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>{{ $movement->reference ?? '-' }}</td>
                                    <td>{{ $movement->user->name }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('clients.current-accounts.edit', [$client, $movement]) }}" 
                                               class="btn btn-primary btn-sm"
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('clients.current-accounts.destroy', [$client, $movement]) }}" 
                                                  method="POST" 
                                                  style="display:inline-block;"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este movimiento?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @if($movement->observations)
                                    <tr>
                                        <td colspan="7">
                                            <div class="alert alert-info mb-0">
                                                <strong>Observaciones:</strong> {{ $movement->observations }}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay movimientos registrados</h5>
                    <p class="text-muted">Este cliente aún no tiene movimientos en su cuenta corriente.</p>
                    <a href="{{ route('clients.current-accounts.create', $client) }}" class="btn btn-success">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 