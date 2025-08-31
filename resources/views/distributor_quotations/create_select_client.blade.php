@extends('layouts.app')

@section('title', 'Seleccionar Cliente para Nuevo Presupuesto')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Seleccionar Cliente para Nuevo Presupuesto</h5>
                    <a href="{{ route('distributor-quotations.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
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

                    <!-- Buscador -->
                    <form action="{{ route('distributor-quotations.create') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                   placeholder="Buscar por nombre, DNI, email o teléfono"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            @if(request('search'))
                                <a href="{{ route('distributor-quotations.create') }}" class="btn btn-light">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            @endif
                        </div>
                    </form>

                    <!-- Lista de Clientes -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>DNI</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributorClients as $client)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $client->full_name }}</div>
                                            @if($client->domicilio)
                                                <small class="text-muted">{{ $client->domicilio }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $client->email ?? 'No registrado' }}</td>
                                        <td>{{ $client->phone ?? 'No registrado' }}</td>
                                        <td>{{ $client->dni ?? 'No registrado' }}</td>
                                        <td>
                                            <a href="{{ route('distributor-clients.quotations.create', $client) }}" 
                                               class="btn btn-success btn-sm">
                                                <i class="fas fa-plus"></i> Crear Presupuesto
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No hay clientes distribuidores registrados.</p>
                                            <a href="{{ route('distributor-clients.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Crear Primer Cliente
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Mostrando {{ $distributorClients->firstItem() ?? 0 }} a {{ $distributorClients->lastItem() ?? 0 }} de {{ $distributorClients->total() }} resultados
                        </div>
                        <div>
                            {{ $distributorClients->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 