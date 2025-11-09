@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Vista Previa de la Disminución de Costos</span>
                        <a href="{{ route('cost-decreases.create') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5 class="alert-heading">Resumen de la Disminución</h5>
                            <p class="mb-0">
                                <strong>Tipo:</strong> {{ $form_data['type'] === 'porcentual' ? 'Porcentual' : 'Fijo' }} |
                                <strong>Valor:</strong> 
                                @if($form_data['type'] === 'porcentual')
                                    {{ number_format($form_data['decrease_value'], 2) }}%
                                @else
                                    ${{ number_format($form_data['decrease_value'], 2) }}
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
                            </p>
                        </div>

                        <div class="mb-4">
                            <h5>Productos que serán afectados ({{ count($preview_data) }})</h5>
                            <p class="text-muted">Revise los cambios antes de confirmar</p>
                        </div>

                        <form action="{{ route('cost-decreases.store') }}" method="POST" id="confirmForm">
                            @csrf
                            
                            <!-- Campos ocultos con los datos del formulario -->
                            <input type="hidden" name="type" value="{{ $form_data['type'] }}">
                            <input type="hidden" name="decrease_value" value="{{ $form_data['decrease_value'] }}">
                            <input type="hidden" name="scope_type" value="{{ $form_data['scope_type'] }}">
                            @if(isset($form_data['supplier_inventory_id']))
                                <input type="hidden" name="supplier_inventory_id" value="{{ $form_data['supplier_inventory_id'] }}">
                            @endif
                            @if(isset($form_data['distributor_brand_id']))
                                <input type="hidden" name="distributor_brand_id" value="{{ $form_data['distributor_brand_id'] }}">
                            @endif

                            <!-- Datos serializados de productos -->
                            <input type="hidden" name="products_data" id="products_data" value="">

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
                                        @foreach($preview_data as $item)
                                            @php
                                                $product = $item['product'];
                                                $previousCost = $item['previous_cost'];
                                                $newCost = $item['new_cost'];
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
                                                    <span class="text-muted text-decoration-line-through">${{ number_format($previousCost, 2) }}</span>
                                                </td>
                                                <td>
                                                    <strong class="text-danger">${{ number_format($newCost, 2) }}</strong>
                                                    <small class="text-danger">
                                                        (-${{ number_format($previousCost - $newCost, 2) }})
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning mt-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Advertencia:</strong> Esta acción no se puede deshacer. Los costos se actualizarán permanentemente.
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('cost-decreases.create') }}" class="btn btn-secondary">Cancelar</a>
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
                        'previous_cost' => $item['previous_cost'],
                        'new_cost' => $item['new_cost']
                    ];
                })->values()->all());
                
                $('#products_data').val(JSON.stringify(productsData));
            });
        </script>
    @endpush
@endsection


