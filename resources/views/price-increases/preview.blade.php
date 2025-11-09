@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Vista Previa del Aumento de Precios</span>
                        <a href="{{ route('price-increases.create') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5 class="alert-heading">Resumen del Aumento</h5>
                            <p class="mb-0">
                                <strong>Tipo:</strong> {{ $form_data['type'] === 'porcentual' ? 'Porcentual' : 'Fijo' }} |
                                <strong>Valor:</strong> 
                                @if($form_data['type'] === 'porcentual')
                                    {{ number_format($form_data['increase_value'], 2) }}%
                                @else
                                    ${{ number_format($form_data['increase_value'], 2) }}
                                @endif
                                |
                                <strong>Alcance:</strong> 
                                @if($form_data['scope_type'] === 'producto')
                                    Producto Individual
                                @elseif($form_data['scope_type'] === 'marca')
                                    Por Marca
                                @else
                                    Varios Productos
                                @endif
                                |
                                <strong>Precios afectados:</strong> 
                                @foreach($form_data['price_types'] as $priceType)
                                    {{ $priceType === 'precio_mayor' ? 'Mayor' : 'Menor' }}
                                    @if(!$loop->last), @endif
                                @endforeach
                            </p>
                        </div>

                        <div class="mb-4">
                            <h5>Productos que serán afectados ({{ count($preview_data) }})</h5>
                            <p class="text-muted">Revise los cambios antes de confirmar</p>
                        </div>

                        <form action="{{ route('price-increases.store') }}" method="POST" id="confirmForm">
                            @csrf
                            
                            <!-- Campos ocultos con los datos del formulario -->
                            <input type="hidden" name="type" value="{{ $form_data['type'] }}">
                            <input type="hidden" name="increase_value" value="{{ $form_data['increase_value'] }}">
                            <input type="hidden" name="scope_type" value="{{ $form_data['scope_type'] }}">
                            @if(isset($form_data['supplier_inventory_id']))
                                <input type="hidden" name="supplier_inventory_id" value="{{ $form_data['supplier_inventory_id'] }}">
                            @endif
                            @if(isset($form_data['distributor_brand_id']))
                                <input type="hidden" name="distributor_brand_id" value="{{ $form_data['distributor_brand_id'] }}">
                            @endif
                            @foreach($form_data['price_types'] as $priceType)
                                <input type="hidden" name="price_types[]" value="{{ $priceType }}">
                            @endforeach

                            <!-- Datos serializados de productos -->
                            <input type="hidden" name="products_data" id="products_data" value="">

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Marca</th>
                                            <th>Precio Mayor</th>
                                            <th>Precio Menor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($preview_data as $item)
                                            @php
                                                $product = $item['product'];
                                                $previous = $item['previous_prices'];
                                                $new = $item['new_prices'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $product->product_name }}</strong>
                                                    @if($product->description)
                                                        <br><small class="text-muted">{{ $product->description }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $product->distributorBrand ? $product->distributorBrand->name : ($product->brand ?? 'Sin marca') }}
                                                </td>
                                                <td>
                                                    @if(in_array('precio_mayor', $form_data['price_types']))
                                                        <div>
                                                            <span class="text-muted text-decoration-line-through">${{ number_format($previous['precio_mayor'], 2) }}</span>
                                                            <br>
                                                            <strong class="text-success">${{ number_format($new['precio_mayor'], 2) }}</strong>
                                                            <small class="text-info">
                                                                (+${{ number_format($new['precio_mayor'] - $previous['precio_mayor'], 2) }})
                                                            </small>
                                                        </div>
                                                    @else
                                                        <span>${{ number_format($previous['precio_mayor'], 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(in_array('precio_menor', $form_data['price_types']))
                                                        <div>
                                                            <span class="text-muted text-decoration-line-through">${{ number_format($previous['precio_menor'], 2) }}</span>
                                                            <br>
                                                            <strong class="text-success">${{ number_format($new['precio_menor'], 2) }}</strong>
                                                            <small class="text-info">
                                                                (+${{ number_format($new['precio_menor'] - $previous['precio_menor'], 2) }})
                                                            </small>
                                                        </div>
                                                    @else
                                                        <span>${{ number_format($previous['precio_menor'], 2) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning mt-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Advertencia:</strong> Esta acción no se puede deshacer. Los precios se actualizarán permanentemente.
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('price-increases.create') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i> Confirmar y Aplicar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Serializar datos de productos para enviar en el formulario
                const productsData = @json(collect($preview_data)->map(function($item) {
                    return [
                        'product_id' => $item['product']->id,
                        'previous_prices' => $item['previous_prices'],
                        'new_prices' => $item['new_prices']
                    ];
                })->values()->all());
                
                $('#products_data').val(JSON.stringify(productsData));
            });
        </script>
    @endpush
@endsection

