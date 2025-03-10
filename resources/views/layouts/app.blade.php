<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Styles -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- Si estás usando Bootstrap directamente desde CDN como respaldo -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Después de los otros estilos -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="inventarioDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('Inventario') }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="inventarioDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('products.index') }}">
                                        {{ __('Productos') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('stock-movements.create') }}">
                                        {{ __('Registrar Movimiento') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- Agrega el dropdown de Clientes -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="clientesDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('Clientes') }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="clientesDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('clients.index') }}">
                                        {{ __('Ver Clientes') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('clients.create') }}">
                                        {{ __('Nuevo Cliente') }}
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Agrega el dropdown de Clientes Distribuidores -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="distribuidoresDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('Clientes Distribuidores') }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="distribuidoresDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('distributor-clients.index') }}">
                                        {{ __('Ver Clientes Distribuidores') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('distributor-clients.create') }}">
                                        {{ __('Nuevo Cliente Distribuidor') }}
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Agrega el dropdown de Inventario Proveedor -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="proveedoresDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('Inventario Proveedor') }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="proveedoresDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('supplier-inventories.index') }}">
                                        {{ __('Ver Inventario Proveedor') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('supplier-inventories.create') }}">
                                        {{ __('Nuevo Inventario Proveedor') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Iniciar sesión') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('register') }}">{{ __('Registrarse') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Cerrar sesión') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
