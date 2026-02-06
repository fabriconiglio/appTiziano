@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-cog me-2"></i>Configuración de Tienda Nube</span>
                    <a href="{{ route('tiendanube.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    {{-- Estado de configuración --}}
                    <div class="alert {{ $isConfigured ? 'alert-success' : 'alert-warning' }}">
                        <div class="d-flex align-items-center">
                            <i class="fas {{ $isConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle' }} fa-2x me-3"></i>
                            <div>
                                <strong>{{ $isConfigured ? 'Configuración Completa' : 'Configuración Incompleta' }}</strong>
                                <p class="mb-0">
                                    {{ $isConfigured ? 'Las credenciales de Tienda Nube están configuradas.' : 'Faltan credenciales de Tienda Nube.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Instrucciones --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Cómo obtener las credenciales</h6>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0">
                                <li class="mb-2">Ingresa a tu panel de administración de Tienda Nube</li>
                                <li class="mb-2">Ve a <strong>Configuración</strong> → <strong>Aplicaciones</strong></li>
                                <li class="mb-2">Busca o crea una aplicación para tu integración</li>
                                <li class="mb-2">Copia el <strong>Access Token</strong> y el <strong>Store ID</strong></li>
                                <li class="mb-0">Agrega las credenciales al archivo <code>.env</code> de tu aplicación</li>
                            </ol>
                        </div>
                    </div>

                    {{-- Variables de entorno requeridas --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-key me-2"></i>Variables de Entorno Requeridas</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Agrega las siguientes líneas a tu archivo <code>.env</code>:</p>
                            
                            <div class="bg-dark text-light p-3 rounded mb-3" style="font-family: monospace;">
                                <div class="mb-2">
                                    <span class="text-info">TIENDANUBE_ACCESS_TOKEN</span>=<span class="text-warning">tu_access_token_aqui</span>
                                    @if($config['has_access_token'])
                                        <span class="badge bg-success ms-2"><i class="fas fa-check"></i> Configurado</span>
                                    @else
                                        <span class="badge bg-danger ms-2"><i class="fas fa-times"></i> Falta</span>
                                    @endif
                                </div>
                                <div class="mb-2">
                                    <span class="text-info">TIENDANUBE_STORE_ID</span>=<span class="text-warning">tu_store_id_aqui</span>
                                    @if($config['has_store_id'])
                                        <span class="badge bg-success ms-2"><i class="fas fa-check"></i> Configurado</span>
                                    @else
                                        <span class="badge bg-danger ms-2"><i class="fas fa-times"></i> Falta</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-info">TIENDANUBE_WEBHOOK_SECRET</span>=<span class="text-warning">tu_secret_opcional</span>
                                    <span class="badge bg-secondary ms-2">Opcional</span>
                                </div>
                            </div>

                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Tip:</strong> Después de modificar el archivo <code>.env</code>, ejecuta <code>php artisan config:clear</code> para aplicar los cambios.
                            </div>
                        </div>
                    </div>

                    {{-- Configuración actual --}}
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Configuración Actual</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td><strong>URL de la API</strong></td>
                                        <td><code>{{ $config['api_url'] }}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Intervalo de Sincronización</strong></td>
                                        <td>Cada {{ $config['sync_interval'] }} horas</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Productos por Lote</strong></td>
                                        <td>{{ $config['batch_size'] }} productos</td>
                                    </tr>
                                    <tr>
                                        <td><strong>URL del Webhook</strong></td>
                                        <td><code>{{ route('webhooks.tiendanube.order-completed') }}</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Comando de sincronización --}}
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-terminal me-2"></i>Comandos Artisan</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Puedes ejecutar la sincronización manualmente desde la terminal:</p>
                            
                            <div class="bg-dark text-light p-3 rounded mb-3" style="font-family: monospace;">
                                <div class="mb-2">
                                    <span class="text-success"># Sincronizar todos los productos</span><br>
                                    php artisan tiendanube:sync
                                </div>
                                <div class="mb-2">
                                    <span class="text-success"># Sincronizar un producto específico</span><br>
                                    php artisan tiendanube:sync --product=123
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
