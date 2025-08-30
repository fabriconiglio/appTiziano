@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Inventario de Proveedores</span>
                        <div>
                            <a href="{{ route('distributor_categories.create') }}" class="btn btn-outline-primary btn-sm me-2">1. Nueva Categoría Distribuidora</a>
                            <a href="{{ route('distributor_brands.create') }}" class="btn btn-outline-primary btn-sm me-2">2. Nueva Marca Distribuidora</a>
                            <a href="{{ route('supplier-inventories.create') }}" class="btn btn-primary btn-sm me-2">3. Nuevo Producto</a>
                            <a href="{{ route('supplier-inventories.export-excel') }}" class="btn btn-success btn-sm me-2">
                                <i class="fas fa-file-excel"></i> Exportar a Excel
                            </a>
                            <a href="{{ route('supplier-inventories.export-lista-mayorista') }}" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf"></i> Lista Mayorista
                            </a>
                            <a href="{{ route('supplier-inventories.export-lista-minorista') }}" class="btn btn-danger btn-sm">
                                <i class="fas fa-file-pdf"></i> Lista Minorista
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="mb-4">
                            <form action="{{ route('supplier-inventories.index') }}" method="GET" class="row">
                                <div class="col-md-4 mb-2">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Buscar por producto, categoría, marca, descripción..." value="{{ request('search') }}">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        @if ($inventories->isEmpty())
                            <div class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-box-open fa-4x mb-3"></i>
                                    <h5>No hay productos en el inventario</h5>
                                    <p class="mb-0">Comienza agregando tu primer producto usando el botón "Nuevo Producto"</p>
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Categoría Distribuidora</th>
                                        <th>Marca Distribuidora</th>
                                        <th>Descripción</th>
                                        <th>Stock</th>
                                        <th>Precio al Mayor</th>
                                        <th>Precio al Menor</th>
                                        <th>Costo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($inventories as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->distributorCategory ? $item->distributorCategory->name : '-' }}</td>
                                        <td>{{ $item->distributorBrand ? $item->distributorBrand->name : '-' }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->stock_quantity }}</td>
                                        <td>${{ number_format($item->precio_mayor, 2) }}</td>
                                        <td>${{ number_format($item->precio_menor, 2) }}</td>
                                        <td>${{ number_format($item->costo, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $item->status_badge_class }}">{{ $item->status_text }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('supplier-inventories.show', $item) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('supplier-inventories.edit', $item) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $item->id }}">
                                                    <i class="fas fa-layer-group"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>

                                            <!-- Modal para ajustar stock -->
                                            <div class="modal fade" id="adjustModal{{ $item->id }}" tabindex="-1" aria-labelledby="adjustModalLabel{{ $item->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('supplier-inventories.adjust-stock', $item) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="adjustModalLabel{{ $item->id }}">Ajustar Stock: {{ $item->product_name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Stock actual: <strong>{{ $item->stock_quantity }}</strong></p>
                                                                <div class="mb-3">
                                                                    <label for="adjustment" class="form-label">Ajuste (positivo para añadir, negativo para restar)</label>
                                                                    <input type="number" class="form-control" id="adjustment" name="adjustment" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="reason" class="form-label">Motivo</label>
                                                                    <textarea class="form-control" id="reason" name="reason" rows="2"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal para eliminar -->
                                            <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('supplier-inventories.destroy', $item) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel{{ $item->id }}">Confirmar eliminación</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                ¿Está seguro de eliminar el producto "{{ $item->product_name }}"?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Mostrando {{ $inventories->firstItem() ?? 0 }} a {{ $inventories->lastItem() ?? 0 }} de {{ $inventories->total() }} resultados
                            </div>
                            <div>
                                {{ $inventories->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
