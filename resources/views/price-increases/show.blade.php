@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Detalles del Aumento de Precios</span>
                        <a href="{{ route('price-increases.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Información del Aumento</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Fecha</th>
                                        <td>{{ $priceIncrease->created_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo de Aumento</th>
                                        <td>
                                            <span class="badge {{ $priceIncrease->type === 'porcentual' ? 'bg-info' : 'bg-success' }}">
                                                {{ $priceIncrease->type_formatted }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Valor</th>
                                        <td>{{ $priceIncrease->value_formatted }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alcance</th>
                                        <td>{{ $priceIncrease->scope_type_formatted }}</td>
                                    </tr>
                                    @if($priceIncrease->scope_type === 'producto' && $priceIncrease->supplierInventory)
                                        <tr>
                                            <th>Producto</th>
                                            <td>{{ $priceIncrease->supplierInventory->product_name }}</td>
                                        </tr>
                                    @endif
                                    @if($priceIncrease->scope_type === 'marca' && $priceIncrease->distributorBrand)
                                        <tr>
                                            <th>Marca</th>
                                            <td>{{ $priceIncrease->distributorBrand->name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Usuario</th>
                                        <td>{{ $priceIncrease->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Precios Afectados</th>
                                        <td>
                                            @foreach($priceIncrease->price_types as $priceType)
                                                <span class="badge bg-secondary">
                                                    {{ $priceType === 'precio_mayor' ? 'Mayor' : 'Menor' }}
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Productos Afectados</th>
                                        <td>{{ count($priceIncrease->affected_products ?? []) }} producto(s)</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Detalle de Productos Afectados</h5>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Marca</th>
                                        <th>Precio Mayor Anterior</th>
                                        <th>Precio Mayor Nuevo</th>
                                        <th>Precio Menor Anterior</th>
                                        <th>Precio Menor Nuevo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $previousPrices = $priceIncrease->previous_prices ?? [];
                                        $newPrices = $priceIncrease->new_prices ?? [];
                                    @endphp
                                    @foreach($priceIncrease->affected_products ?? [] as $productId)
                                        @php
                                            $product = $products[$productId] ?? null;
                                            $prev = $previousPrices[$productId] ?? ['precio_mayor' => 0, 'precio_menor' => 0];
                                            $new = $newPrices[$productId] ?? ['precio_mayor' => 0, 'precio_menor' => 0];
                                        @endphp
                                        @if($product)
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
                                                    @if(in_array('precio_mayor', $priceIncrease->price_types ?? []))
                                                        <span class="text-muted">${{ number_format($prev['precio_mayor'] ?? 0, 2) }}</span>
                                                    @else
                                                        <span>${{ number_format($prev['precio_mayor'] ?? 0, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(in_array('precio_mayor', $priceIncrease->price_types ?? []))
                                                        <strong class="text-success">${{ number_format($new['precio_mayor'] ?? 0, 2) }}</strong>
                                                    @else
                                                        <span>${{ number_format($new['precio_mayor'] ?? 0, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(in_array('precio_menor', $priceIncrease->price_types ?? []))
                                                        <span class="text-muted">${{ number_format($prev['precio_menor'] ?? 0, 2) }}</span>
                                                    @else
                                                        <span>${{ number_format($prev['precio_menor'] ?? 0, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(in_array('precio_menor', $priceIncrease->price_types ?? []))
                                                        <strong class="text-success">${{ number_format($new['precio_menor'] ?? 0, 2) }}</strong>
                                                    @else
                                                        <span>${{ number_format($new['precio_menor'] ?? 0, 2) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

