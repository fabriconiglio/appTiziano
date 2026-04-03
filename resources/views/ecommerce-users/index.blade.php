@extends('layouts.app')

@section('title', 'Usuarios E-Commerce')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="fs-4 fw-bold mb-0">
                    <i class="fas fa-users me-2"></i>Usuarios Registrados del E-Commerce
                </h2>
                <span class="badge bg-primary fs-6">{{ $users->total() }} usuarios</span>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="GET" action="{{ route('ecommerce-users.index') }}" class="mb-4">
                    <div class="input-group" style="max-width: 400px;">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o email..."
                               value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ route('ecommerce-users.index') }}" class="btn btn-outline-danger" title="Limpiar">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px;">ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Email verificado</th>
                                <th>Fecha de registro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $user->id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                                    </td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Verificado
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-clock me-1"></i>Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $user->created_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-users fa-2x text-muted mb-2 d-block"></i>
                                        @if(request('search'))
                                            No se encontraron usuarios con "{{ request('search') }}".
                                        @else
                                            Todavía no hay usuarios registrados en el e-commerce.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Mostrando {{ $users->firstItem() ?? 0 }} a {{ $users->lastItem() ?? 0 }} de {{ $users->total() }} resultados
                        </div>
                        <div>
                            {{ $users->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
