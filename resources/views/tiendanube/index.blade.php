@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-cloud me-2"></i>Tienda Nube
                    </h1>
                    <p class="text-muted mb-0">Sincronización de productos con tu tienda online</p>
                </div>
                <div>
                    <a href="{{ route('tiendanube.config') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-cog me-1"></i> Configuración
                    </a>
                    <a href="{{ route('supplier-inventories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Inventario
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Estado de Conexión --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card {{ $isConfigured ? 'border-success' : 'border-danger' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        @if($isConfigured)
                            <div class="flex-shrink-0">
                                <span class="badge bg-success p-2 rounded-circle">
                                    <i class="fas fa-check fa-lg"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">Conexión Configurada</h5>
                                <p class="mb-0 text-muted">Las credenciales de Tienda Nube están configuradas correctamente.</p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-success" id="test-connection-btn">
                                    <i class="fas fa-plug me-1"></i> Probar Conexión
                                </button>
                            </div>
                        @else
                            <div class="flex-shrink-0">
                                <span class="badge bg-danger p-2 rounded-circle">
                                    <i class="fas fa-times fa-lg"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-1">Conexión No Configurada</h5>
                                <p class="mb-0 text-muted">Configura las credenciales de Tienda Nube en el archivo <code>.env</code></p>
                            </div>
                            <div>
                                <a href="{{ route('tiendanube.config') }}" class="btn btn-warning">
                                    <i class="fas fa-cog me-1"></i> Configurar
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Para Publicar</h6>
                            <h2 class="mb-0">{{ $stats['total_para_publicar'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-box fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Sincronizados</h6>
                            <h2 class="mb-0">{{ $stats['sincronizados'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-dark opacity-75">Pendientes</h6>
                            <h2 class="mb-0">{{ $stats['pendientes'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50">Sin Sincronizar</h6>
                            <h2 class="mb-0">{{ $stats['sin_sincronizar'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cloud-upload-alt fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Acciones --}}
    @if($isConfigured)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sync me-2"></i>Acciones de Sincronización</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('tiendanube.sync-all') }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de sincronizar todos los productos? Esto puede tardar varios minutos.')">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100" {{ $stats['total_para_publicar'] == 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-sync me-2"></i>Sincronizar Todos los Productos
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">Sincroniza todos los productos marcados para Tienda Nube</small>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-primary btn-lg w-100" id="register-webhook-btn">
                                <i class="fas fa-link me-2"></i>Registrar Webhook
                            </button>
                            <small class="text-muted d-block mt-2">Registra el webhook para actualización automática de stock</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        {{-- Productos Pendientes --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock text-warning me-2"></i>Pendientes de Sincronización</h5>
                    <span class="badge bg-warning text-dark">{{ $pendingProducts->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($pendingProducts->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">No hay productos pendientes</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingProducts as $product)
                                    <tr>
                                        <td>
                                            <strong>{{ Str::limit($product->product_name, 30) }}</strong>
                                            @if($product->distributorBrand)
                                                <br><small class="text-muted">{{ $product->distributorBrand->name }}</small>
                                            @endif
                                        </td>
                                        <td>${{ number_format($product->precio_menor ?? 0, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $product->stock_quantity > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $product->stock_quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary sync-product-btn" data-id="{{ $product->id }}">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Productos Sincronizados Recientemente --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Sincronizados Recientemente</h5>
                    <span class="badge bg-success">{{ $recentlySynced->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($recentlySynced->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                            <p class="mb-0">Aún no hay productos sincronizados</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Última Sync</th>
                                        <th>ID Tienda Nube</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentlySynced as $product)
                                    <tr>
                                        <td>
                                            <strong>{{ Str::limit($product->product_name, 30) }}</strong>
                                            @if($product->distributorBrand)
                                                <br><small class="text-muted">{{ $product->distributorBrand->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $product->tiendanube_synced_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <code>{{ $product->tiendanube_product_id }}</code>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de resultado de conexión --}}
<div class="modal fade" id="connectionResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resultado de Conexión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="connection-result-body">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Probar conexión
    $('#test-connection-btn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Probando...');
        
        $.ajax({
            url: '{{ route("tiendanube.test-connection") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                let html = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + response.message + '</div>';
                if (response.store_info) {
                    html += '<p><strong>Tienda:</strong> ' + (response.store_info.name?.es || 'N/A') + '</p>';
                }
                $('#connection-result-body').html(html);
                $('#connectionResultModal').modal('show');
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Error desconocido';
                $('#connection-result-body').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + error + '</div>');
                $('#connectionResultModal').modal('show');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-plug me-1"></i> Probar Conexión');
            }
        });
    });

    // Sincronizar producto individual
    $('.sync-product-btn').on('click', function() {
        const btn = $(this);
        const productId = btn.data('id');
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: '/tiendanube/sync/' + productId,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    btn.removeClass('btn-outline-primary').addClass('btn-success').html('<i class="fas fa-check"></i>');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Error al sincronizar';
                alert('Error: ' + error);
                btn.prop('disabled', false).html('<i class="fas fa-sync"></i>');
            }
        });
    });

    // Registrar webhook
    $('#register-webhook-btn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Registrando...');
        
        $.ajax({
            url: '{{ route("tiendanube.register-webhook") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                let html = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + response.message + '</div>';
                html += '<p><strong>URL del Webhook:</strong><br><code>' + response.webhook_url + '</code></p>';
                $('#connection-result-body').html(html);
                $('#connectionResultModal').modal('show');
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Error desconocido';
                $('#connection-result-body').html('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>' + error + '</div>');
                $('#connectionResultModal').modal('show');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-link me-2"></i>Registrar Webhook');
            }
        });
    });
});
</script>
@endpush
