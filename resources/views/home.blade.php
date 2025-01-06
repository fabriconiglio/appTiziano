@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <img src="{{ asset('images/tiziano-logo.png') }}"
                     alt="Tiziano - Artículos de Peluquería"
                     class="img-fluid mb-4"
                     style="max-width: 400px;">

                @auth
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-3">Bienvenido/a, {{ Auth::user()->name }}</h4>
                            <p class="text-muted">Sistema de Gestión de Peluquería</p>

                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <a href="{{ route('clients.index') }}" class="btn btn-outline-primary w-100 mb-2">
                                        <i class="fas fa-users mb-2 d-block" style="font-size: 24px;"></i>
                                        Clientes
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary w-100 mb-2">
                                        <i class="fas fa-box mb-2 d-block" style="font-size: 24px;"></i>
                                        Inventario
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ route('clients.create') }}" class="btn btn-outline-primary w-100 mb-2">
                                        <i class="fas fa-user-plus mb-2 d-block" style="font-size: 24px;"></i>
                                        Nuevo Cliente
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
@endsection
