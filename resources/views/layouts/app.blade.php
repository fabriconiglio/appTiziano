<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('favicon.ico') }}?v=2">

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

    <!-- CSS de Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @stack('styles')

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
                        <!-- Módulo de Peluquería -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="peluqueriaDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('Peluquería') }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="peluqueriaDropdown">
                                <!-- Submenú Inventario Peluquería -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('products.index') }}">{{ __('Inventario') }}</a>
                                </li>
                                <!-- Submenú Clientes Peluquería -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('clients.index') }}">{{ __('Clientes') }}</a>
                                </li>

                                <!-- Submenú Categorías Peluquería -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('categories.index') }}">{{ __('Categorías') }}</a>
                                </li>

                                <!-- Submenú Marcas Peluquería -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('brands.index') }}">{{ __('Marcas') }}</a>
                                </li>

                                <!-- Submenú Alertas de Stock -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('stock-alerts.peluqueria') }}">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        {{ __('Alertas de Stock') }}
                                        <span class="badge bg-danger ms-1" id="alert-count">0</span>
                                    </a>
                                </li>

                            </ul>
                        </li>

                        <!-- Módulo de Distribuidora -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="distribuidoraDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('Distribuidora') }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="distribuidoraDropdown">
                                <!-- Submenú Clientes Distribuidora -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('distributor-clients.index') }}">{{ __('Clientes Distribuidores') }}</a>
                                </li>
                                <!-- Submenú Inventario Proveedor -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('supplier-inventories.index') }}">{{ __('Inventario Proveedor') }}</a>
                                </li>
                                <!-- Submenú Categorías Distribuidora -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('distributor_categories.index') }}">{{ __('Categorías Distribuidora') }}</a>
                                </li>
                                <!-- Submenú Marcas Distribuidora -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('distributor_brands.index') }}">{{ __('Marcas Distribuidora') }}</a>
                                </li>
                                <!-- Submenú Alertas de Stock -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item" href="{{ route('stock-alerts.distribuidora') }}">
                                        <i class="fas fa-exclamation-triangle text-warning"></i>
                                        {{ __('Alertas de Stock') }}
                                        <span class="badge bg-danger ms-1" id="alert-count-distribuidora">0</span>
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


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Luego Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@stack('scripts')

@auth
<script>
// Actualizar contador de alertas de peluquería
function updatePeluqueriaAlertCount() {
    $.get('{{ route("stock-alerts.unread-count-peluqueria") }}')
        .done(function(data) {
            $('#alert-count').text(data.count);
            if (data.count > 0) {
                $('#alert-count').removeClass('d-none');
            } else {
                $('#alert-count').addClass('d-none');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error al obtener alertas de peluquería:', error);
        });
}

// Actualizar contador de alertas de distribuidora
function updateDistribuidoraAlertCount() {
    $.get('{{ route("stock-alerts.unread-count-distribuidora") }}')
        .done(function(data) {
            $('#alert-count-distribuidora').text(data.count);
            if (data.count > 0) {
                $('#alert-count-distribuidora').removeClass('d-none');
            } else {
                $('#alert-count-distribuidora').addClass('d-none');
            }
        })
        .fail(function(xhr, status, error) {
            console.error('Error al obtener alertas de distribuidora:', error);
        });
}

// Función para actualizar ambos contadores
function updateAllAlertCounts() {
    updatePeluqueriaAlertCount();
    updateDistribuidoraAlertCount();
}

// Actualizar al cargar la página
console.log('Página cargada, iniciando actualización de alertas...');
updateAllAlertCounts();

// Actualizar cada 30 segundos
setInterval(updateAllAlertCounts, 30000);
</script>
@endauth

</body>
</html>
