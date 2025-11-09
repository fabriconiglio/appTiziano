@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Historial de Disminuciones de Precios</span>
                        <a href="{{ route('price-decreases.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Nueva Disminución
                        </a>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Filtros -->
                        <form action="{{ route('price-decreases.index') }}" method="GET" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="type" class="form-label">Tipo de Disminución</label>
                                    <select name="type" id="type" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="porcentual" {{ request('type') == 'porcentual' ? 'selected' : '' }}>Porcentual</option>
                                        <option value="fijo" {{ request('type') == 'fijo' ? 'selected' : '' }}>Fijo</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="scope_type" class="form-label">Alcance</label>
                                    <select name="scope_type" id="scope_type" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="producto" {{ request('scope_type') == 'producto' ? 'selected' : '' }}>Producto Individual</option>
                                        <option value="marca" {{ request('scope_type') == 'marca' ? 'selected' : '' }}>Por Marca</option>
                                        <option value="multiples" {{ request('scope_type') == 'multiples' ? 'selected' : '' }}>Varios Productos</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="user_id" class="form-label">Usuario</label>
                                    <select name="user_id" id="user_id" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">Fecha Desde</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">Fecha Hasta</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-filter me-1"></i> Filtrar
                                    </button>
                                    <a href="{{ route('price-decreases.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                        </form>

                        @if ($histories->isEmpty())
                            <div class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-history fa-4x mb-3"></i>
                                    <h5>No hay registros de disminuciones</h5>
                                    <p>Las disminuciones de precios aparecerán aquí</p>
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th>Alcance</th>
                                            <th>Productos Afectados</th>
                                            <th>Usuario</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($histories as $history)
                                            <tr>
                                                <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span class="badge {{ $history->type === 'porcentual' ? 'bg-info' : 'bg-success' }}">
                                                        {{ $history->type_formatted }}
                                                    </span>
                                                </td>
                                                <td>{{ $history->value_formatted }}</td>
                                                <td>{{ $history->scope_type_formatted }}</td>
                                                <td>{{ count($history->affected_products ?? []) }} producto(s)</td>
                                                <td>{{ $history->user->name }}</td>
                                                <td>
                                                    <a href="{{ route('price-decreases.show', $history) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                {{ $histories->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


