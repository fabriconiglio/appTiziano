@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Detalles del Producto: {{ $supplierInventory->product_name }}</span>
                        <div>
                            <a href="{{ route('supplier-inventories.edit', $supplierInventory) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="{{ route('supplier-inventories.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Información del Producto</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <strong>Nombre del Producto:</strong>
                                        <p>{{ $supplierInventory->product_name }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Categoría Distribuidora:</strong>
                                        <p>{{ $supplierInventory->distributorCategory ? $supplierInventory->distributorCategory->name : 'No disponible' }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Marca Distribuidora:</strong>
                                        <p>{{ $supplierInventory->distributorBrand ? $supplierInventory->distributorBrand->name : 'No disponible' }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Precio de Venta:</strong>
                                        <p>${{ number_format($supplierInventory->price, 2) }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Precio de Compra:</strong>
                                        <p>{{ $supplierInventory->purchase_price ? '$' . number_format($supplierInventory->purchase_price, 2) : 'No disponible' }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Precio al Mayor:</strong>
                                        <p>{{ $supplierInventory->precio_mayor ? '$' . number_format($supplierInventory->precio_mayor, 2) : 'No disponible' }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Precio al Menor:</strong>
                                        <p>{{ $supplierInventory->precio_menor ? '$' . number_format($supplierInventory->precio_menor, 2) : 'No disponible' }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Costo:</strong>
                                        <p>{{ $supplierInventory->costo ? '$' . number_format($supplierInventory->costo, 2) : 'No disponible' }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Cantidad en Inventario:</strong>
                                        <p>{{ $supplierInventory->stock_quantity }}</p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Estado:</strong>
                                        <p>
                                            @if ($supplierInventory->status == 'available')
                                                <span class="badge bg-success">Disponible</span>
                                            @elseif ($supplierInventory->status == 'low_stock')
                                                <span class="badge bg-warning">Bajo stock</span>
                                            @else
                                                <span class="badge bg-danger">Sin stock</span>
                                            @endif
                                        </p>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Fecha de Última Reposición:</strong>
                                        <p>{{ $supplierInventory->last_restock_date ? $supplierInventory->last_restock_date->format('d/m/Y') : 'No disponible' }}</p>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <strong>Descripción:</strong>
                                        <p>{{ $supplierInventory->description ?? 'Sin descripción' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Información Adicional</h5>
                                <hr>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <strong>Notas:</strong>
                                        <p>{{ strip_tags($supplierInventory->notes ?? 'Sin observaciones') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Ajustar Stock</h5>
                                <hr>
                                <form action="{{ route('supplier-inventories.adjust-stock', $supplierInventory) }}" method="POST" class="row">
                                    @csrf
                                    <div class="col-md-4 mb-3">
                                        <label for="adjustment" class="form-label">Ajuste (+ para añadir, - para restar)</label>
                                        <input type="number" class="form-control" id="adjustment" name="adjustment" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="reason" class="form-label">Motivo</label>
                                        <input type="text" class="form-control" id="reason" name="reason">
                                    </div>
                                    <div class="col-md-2 mb-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Ajustar</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('supplier-inventories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a la lista
                            </a>
                            <div>
                                <a href="{{ route('supplier-inventories.edit', $supplierInventory) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Editar Producto
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i> Eliminar Producto
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('supplier-inventories.destroy', $supplierInventory) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ¿Está seguro de eliminar este producto: <strong>{{ $supplierInventory->product_name }}</strong>?
                        <br><br>
                        Esta acción no se puede deshacer.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
