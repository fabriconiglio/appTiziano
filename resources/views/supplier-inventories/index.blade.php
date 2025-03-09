@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Inventario de Proveedores</span>
                        <a href="{{ route('supplier-inventories.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Producto
                        </a>
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
                                        <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select name="category" class="form-select">
                                        <option value="">Todas las categorías</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select name="supplier" class="form-select">
                                        <option value="">Todos los proveedores</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier }}" {{ request('supplier') == $supplier ? 'selected' : '' }}>
                                                {{ $supplier }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <select name="status" class="form-select">
                                        <option value="">Todos los estados</option>
                                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Bajo stock</option>
                                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Sin stock</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>SKU</th>
                                    <th>Proveedor</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($inventories as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td>{{ $item->sku }}</td>
                                        <td>{{ $item->supplier_name }}</td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td>{{ $item->stock_quantity }}</td>
                                        <td>
                                            @if ($item->status == 'available')
                                                <span class="badge bg-success">Disponible</span>
                                            @elseif ($item->status == 'low_stock')
                                                <span class="badge bg-warning">Bajo stock</span>
                                            @else
                                                <span class="badge bg-danger">Sin stock</span>
                                            @endif
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
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay productos en el inventario</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $inventories->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
