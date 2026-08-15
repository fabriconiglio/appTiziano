@extends('layouts.app')

@section('title', 'Devoluciones - Distribuidora')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Devoluciones</h1>
        <div>
            <a href="{{ route('distributor-clients.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('distributor-returns.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Devolución
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1">Buscar</label>
                    <input type="text" name="buscar" value="{{ $buscar }}" class="form-control"
                           placeholder="Número de devolución o cliente">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary w-100"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th class="text-end">Monto</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $devolucion)
                        <tr class="{{ $devolucion->estaAnulada() ? 'text-muted' : '' }}">
                            <td>
                                <strong class="{{ $devolucion->estaAnulada() ? 'text-decoration-line-through' : '' }}">
                                    {{ $devolucion->return_number }}
                                </strong>
                            </td>
                            <td>{{ $devolucion->return_date->format('d/m/Y') }}</td>
                            <td>{{ $devolucion->nombreCliente() }}</td>
                            <td class="text-end">${{ number_format($devolucion->total_amount, 2, ',', '.') }}</td>
                            <td>
                                @switch($devolucion->destino)
                                    @case('cuenta_corriente')
                                        <span class="badge bg-info text-dark">Cuenta corriente</span> @break
                                    @case('efectivo')
                                        <span class="badge bg-success">Efectivo</span> @break
                                    @default
                                        <span class="badge bg-warning text-dark">Vale a favor</span>
                                @endswitch
                            </td>
                            <td>
                                @if($devolucion->estaAnulada())
                                    <span class="badge bg-danger">Anulada</span>
                                @elseif($devolucion->fuera_de_plazo)
                                    <span class="badge bg-warning text-dark" title="Se autorizó fuera del plazo">
                                        Fuera de plazo
                                    </span>
                                @else
                                    <span class="badge bg-success">Vigente</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('distributor-returns.show', $devolucion) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Todavía no hay devoluciones cargadas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $returns->links() }}</div>
</div>
@endsection
