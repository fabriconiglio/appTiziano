@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Detalles de la Disminución de Costos</span>
                        <a href="{{ route('cost-decreases.index') }}" class="btn btn-secondary btn-sm">
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
                                        <td>{{ $costDecrease->created_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tipo de Disminución</th>
                                        <td>
                                            <span class="badge {{ $costDecrease->type === 'porcentual' ? 'bg-info' : 'bg-success' }}">
                                                {{ $costDecrease->type_formatted }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Valor</th>
                                        <td>{{ $costDecrease->value_formatted }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alcance</th>
                                        <td>{{ $costDecrease->scope_type_formatted }}</td>
                                    </tr>
                                    @if($costDecrease->scope_type === 'producto' && $costDecrease->supplierInventory)
                                        <tr>
                                            <th>Producto</th>
                                            <td>{{ $costDecrease->supplierInventory->product_name }}</td>
                                        </tr>
                                    @endif
                                    @if($costDecrease->scope_type === 'marca' && $costDecrease->distributorBrand)
                                        <tr>
                                            <th>Marca</th>
                                            <td>{{ $costDecrease->distributorBrand->name }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Usuario</th>
                                        <td>{{ $costDecrease->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Productos Afectados</th>
                                        <td>{{ count($costDecrease->affected_products ?? []) }} producto(s)</td>
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
                                        $previousValues = $costDecrease->previous_values ?? [];
                                        $newValues = $costDecrease->new_values ?? [];
                                    @endphp
                                    @foreach($costDecrease->affected_products ?? [] as $productId)
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
                                                    <strong class="text-danger">${{ number_format($newCost, 2) }}</strong>
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


