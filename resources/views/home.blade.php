@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 text-center">
                <img src="{{ asset('images/tiziano-logo-final.jpeg') }}"
                     alt="Tiziano - Artículos de Peluquería"
                     class="img-fluid mb-4"
                     style="max-width: 300px;">

                @auth
                    <h4 class="mb-4">Bienvenido/a, {{ Auth::user()->name }}</h4>

                    <div class="row">
                        <!-- Módulo de Peluquería -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-primary text-black">
                                    <h5 class="mb-0">
                                        <i class="fas fa-cut me-2"></i>
                                        Módulo Peluquería
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-4">Gestión de clientes e inventario de peluquería</p>

                                    <div class="d-grid gap-3">
                                        <a href="{{ route('clients.index') }}"
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-users me-2"></i>
                                            Clientes Peluquería
                                        </a>

                                        <a href="{{ route('products.index') }}"
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-box me-2"></i>
                                            Inventario Peluquería
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Módulo de Distribuidora -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-success text-black">
                                    <h5 class="mb-0">
                                        <i class="fas fa-warehouse me-2"></i>
                                        Módulo Distribuidora
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-4">Gestión de clientes distribuidores e inventario mayorista</p>

                                    <div class="d-grid gap-3">
                                        <a href="{{ route('distributor-clients.index') }}"
                                           class="btn btn-outline-success">
                                            <i class="fas fa-users-cog me-2"></i>
                                            Clientes Distribuidores
                                        </a>

                                        <a href="{{ route('supplier-inventories.index') }}"
                                           class="btn btn-outline-success">
                                            <i class="fas fa-box-open me-2"></i>
                                            Inventario Mayorista
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth

                @guest
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Bienvenido a Tiziano</h5>
                            <p class="card-text">Por favor, inicia sesión para acceder al sistema.</p>
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Iniciar Sesión
                            </a>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease-in-out;
        border: none;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-header {
        border-bottom: none;
        padding: 1rem;
    }

    .btn {
        padding: 0.75rem 1.25rem;
        font-weight: 500;
    }

    .btn-outline-primary:hover,
    .btn-outline-success:hover {
        transform: scale(1.02);
    }

    .shadow-sm {
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
    }
</style>
@endpush
