@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Agregar Nuevo Producto al Inventario</span>
                        <a href="{{ route('supplier-inventories.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('supplier-inventories.store') }}" method="POST">
                            @csrf

                            <div class="row mb-4">
                                <h5>Información del Producto</h5>
                                <hr>
                                <div class="col-md-6 mb-3">
                                    <label for="product_name" class="form-label">Nombre del Producto *</label>
                                    <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name') }}" required>
                                    @error('product_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}">
                                    @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Categoría</label>
                                    <input type="text" class="form-control @error('category') is-invalid @enderror" id="category" name="category" value="{{ old('category') }}">
                                    @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="brand" class="form-label">Marca</label>
                                    <input type="text" class="form-control @error('brand') is-invalid @enderror" id="brand" name="brand" value="{{ old('brand') }}">
                                    @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Precio de Venta *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                                        @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="purchase_price" class="form-label">Precio de Compra</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('purchase_price') is-invalid @enderror" id="purchase_price" name="purchase_price" value="{{ old('purchase_price') }}">
                                        @error('purchase_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="stock_quantity" class="form-label">Cantidad en Inventario *</label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required>
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_restock_date" class="form-label">Fecha de Última Reposición</label>
                                    <input type="date" class="form-control @error('last_restock_date') is-invalid @enderror" id="last_restock_date" name="last_restock_date" value="{{ old('last_restock_date') }}">
                                    @error('last_restock_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <h5>Información del Proveedor</h5>
                                <hr>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_name" class="form-label">Nombre del Proveedor *</label>
                                    <input type="text" class="form-control @error('supplier_name') is-invalid @enderror" id="supplier_name" name="supplier_name" value="{{ old('supplier_name') }}" required>
                                    @error('supplier_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="supplier_contact" class="form-label">Contacto del Proveedor</label>
                                    <input type="text" class="form-control @error('supplier_contact') is-invalid @enderror" id="supplier_contact" name="supplier_contact" value="{{ old('supplier_contact') }}">
                                    @error('supplier_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="supplier_email" class="form-label">Email del Proveedor</label>
                                    <input type="email" class="form-control @error('supplier_email') is-invalid @enderror" id="supplier_email" name="supplier_email" value="{{ old('supplier_email') }}">
                                    @error('supplier_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="supplier_phone" class="form-label">Teléfono del Proveedor</label>
                                    <input type="text" class="form-control @error('supplier_phone') is-invalid @enderror" id="supplier_phone" name="supplier_phone" value="{{ old('supplier_phone') }}">
                                    @error('supplier_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <h5>Información Adicional</h5>
                                <hr>
                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">Notas</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('supplier-inventories.index') }}" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Producto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
