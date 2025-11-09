@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Detalles del Aumento de Costos</span>
                        <a href="{{ route('cost-increases.index') }}" class="btn btn-secondary btn-sm">
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
                                        <td>{{ $costIncrease->created_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo de Aumento</th>
                                        <td>
                                            <span class="badge {{ $costIncrease->type === 'porcentual' ? 'bg-info' : 'bg-success' }}">
                                                {{ $costIncrease->type_formatted }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Valor</th>
                                        <td>{{ $costIncrease->value_formatted }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alcance</th>
                                        <td>{{ $costIncrease->scope_type_formatted }}</td>
                                    </tr>
                                    @if($costIncrease->scope_type === 'producto' && $costIncrease->supplierInventory)
                                        <tr>
                                            <th>Producto</th>
                                            <td>{{ $costIncrease->supplierInventory->product_name }}</td>
                                        </tr>
                                    @endif
                                    @if($costIncrease->scope_type === 'marca' && $costIncrease->distributorBrand)
                                        <tr>
                                            <th>Marca</th>
                                            <td>{{ $costIncrease->distributorBrand->name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Usuario</th>
                                        <td>{{ $costIncrease->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Productos Afectados</th>
                                        <td>{{ count($costIncrease->affected_products ?? []) }} producto(s)</td>
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
                                        <th>Costo Anterior</th>
                                        <th>Costo Nuevo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $previousValues = $costIncrease->previous_values ?? [];
                                        $newValues = $costIncrease->new_values ?? [];
                                    @endphp
                                    @foreach($costIncrease->affected_products ?? [] as $productId)
                                        @php
                                            $product = $products[$productId] ?? null;
                                            $prevCost = $previousValues[$productId] ?? 0;
                                            $newCost = $newValues[$productId] ?? 0;
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
                                                    <span class="text-muted">${{ number_format($prevCost, 2) }}</span>
                                                </td>
                                                <td>
                                                    <strong class="text-success">${{ number_format($newCost, 2) }}</strong>
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

