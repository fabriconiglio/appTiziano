@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Detalles de la Disminución de Precios</span>
                        <a href="{{ route('price-decreases.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Información de la Disminución</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Fecha</th>
                                        <td>{{ $priceDecrease->created_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo de Disminución</th>
                                        <td>
                                            <span class="badge {{ $priceDecrease->type === 'porcentual' ? 'bg-info' : 'bg-success' }}">
                                                {{ $priceDecrease->type_formatted }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Valor</th>
                                        <td>{{ $priceDecrease->value_formatted }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alcance</th>
                                        <td>{{ $priceDecrease->scope_type_formatted }}</td>
                                    </tr>
                                    @if($priceDecrease->scope_type === 'producto' && $priceDecrease->supplierInventory)
                                        <tr>
                                            <th>Producto</th>
                                            <td>{{ $priceDecrease->supplierInventory->product_name }}</td>
                                        </tr>
                                    @endif
                                    @if($priceDecrease->scope_type === 'marca' && $priceDecrease->distributorBrand)
                                        <tr>
                                            <th>Marca</th>
                                            <td>{{ $priceDecrease->distributorBrand->name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Usuario</th>
                                        <td>{{ $priceDecrease->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Precios Afectados</th>
                                        <td>
                                            @foreach($priceDecrease->price_types as $priceType)
                                                <span class="badge bg-secondary">
                                                    {{ $priceType === 'precio_mayor' ? 'Mayor' : 'Menor' }}
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Productos Afectados</th>
                                        <td>{{ count($priceDecrease->affected_products ?? []) }} producto(s)</td>
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
                                        $previousValues = $priceDecrease->previous_values ?? [];
                                        $newValues = $priceDecrease->new_values ?? [];
                                    @endphp
                                    @foreach($priceDecrease->affected_products ?? [] as $productId)
                                        @php
                                            $product = $products[$productId] ?? null;
                                            $prev = $previousValues[$productId] ?? ['precio_mayor' => 0, 'precio_menor' => 0];
                                            $new = $newValues[$productId] ?? ['precio_mayor' => 0, 'precio_menor' => 0];
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
                                                    @if(in_array('precio_mayor', $priceDecrease->price_types ?? []))
                                                        <span class="text-muted">${{ number_format($prev['precio_mayor'] ?? 0, 2) }}</span>
                                                    @else
                                                        <span>${{ number_format($prev['precio_mayor'] ?? 0, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(in_array('precio_mayor', $priceDecrease->price_types ?? []))
                                                        <strong class="text-danger">${{ number_format($new['precio_mayor'] ?? 0, 2) }}</strong>
                                                    @else
                                                        <span>${{ number_format($new['precio_mayor'] ?? 0, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(in_array('precio_menor', $priceDecrease->price_types ?? []))
                                                        <span class="text-muted">${{ number_format($prev['precio_menor'] ?? 0, 2) }}</span>
                                                    @else
                                                        <span>${{ number_format($prev['precio_menor'] ?? 0, 2) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(in_array('precio_menor', $priceDecrease->price_types ?? []))
                                                        <strong class="text-danger">${{ number_format($new['precio_menor'] ?? 0, 2) }}</strong>
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


