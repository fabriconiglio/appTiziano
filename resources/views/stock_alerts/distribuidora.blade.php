@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-warehouse text-success me-2"></i>
                        Alertas de Stock - Distribuidora
                    </h5>
                    <div>
                        <button type="button" class="btn btn-success btn-sm" id="mark-all-read-distribuidora">
                            <i class="fas fa-check-double"></i> Marcar todas como leídas
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if($alerts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Tipo</th>
                                        <th>Stock Actual</th>
                                        <th>Umbral</th>
                                        <th>Mensaje</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alerts as $alert)
                                        <tr class="{{ $alert->is_read ? 'table-light' : 'table-warning' }}">
                                            <td>
                                                <strong>{{ $alert->supplierInventory ? $alert->supplierInventory->product_name : 'Producto no encontrado' }}</strong>
                                                @if($alert->supplierInventory && $alert->supplierInventory->sku)
                                                    <br><small class="text-muted">SKU: {{ $alert->supplierInventory->sku }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($alert->type === 'low_stock')
                                                    <span class="badge bg-warning text-dark">Stock Bajo</span>
                                                @else
                                                    <span class="badge bg-danger">Sin Stock</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="fw-bold {{ $alert->current_stock == 0 ? 'text-danger' : 'text-warning' }}">
                                                    {{ $alert->current_stock }}
                                                </span>
                                            </td>
                                            <td>{{ $alert->threshold }}</td>
                                            <td>{{ $alert->message }}</td>
                                            <td>{{ $alert->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($alert->is_read)
                                                    <span class="badge bg-success">Leída</span>
                                                @else
                                                    <span class="badge bg-primary">Nueva</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if(!$alert->is_read)
                                                        <button type="button" class="btn btn-sm btn-outline-success mark-read" 
                                                                data-alert-id="{{ $alert->id }}" title="Marcar como leída">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                    @if($alert->supplierInventory)
                                                        <a href="{{ route('supplier-inventories.edit', $alert->supplierInventory) }}" 
                                                           class="btn btn-sm btn-outline-primary" title="Editar producto">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('stock-alerts.destroy', $alert) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('¿Estás seguro?')" title="Eliminar alerta">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $alerts->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <h5>No hay alertas de stock en Distribuidora</h5>
                            <p class="text-muted">Todos los productos de distribuidora tienen stock suficiente.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Marcar alerta como leída
    $('.mark-read').click(function() {
        const alertId = $(this).data('alert-id');
        const button = $(this);
        
        $.ajax({
            url: `/stock-alerts/${alertId}/mark-read`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    button.closest('tr').removeClass('table-warning').addClass('table-light');
                    button.closest('td').find('.badge').removeClass('bg-primary').addClass('bg-success').text('Leída');
                    button.remove();
                }
            }
        });
    });

    // Marcar todas como leídas (solo distribuidora)
    $('#mark-all-read-distribuidora').click(function() {
        $.ajax({
            url: '{{ route("stock-alerts.mark-all-read-by-type") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                type: 'distribuidora'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>
@endpush
@endsection 