@extends('layouts.app')

@section('title', 'Crear Producto')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">Crear Producto</h2>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>¡Error!</strong> Por favor corrige los siguientes errores:
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('products.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nombre</label>
                                    <input type="text"
                                           name="name"
                                           id="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    {{--
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text"
                                           name="sku"
                                           id="sku"
                                           class="form-control @error('sku') is-invalid @enderror"
                                           value="{{ old('sku') }}">
                                    @error('sku')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    --}}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Categoría</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                            id="category_id"
                                            name="category_id"
                                            required>
                                        <option value="">Selecciona una categoría</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="brand_id" class="form-label">Marca</label>
                                    <select class="form-select @error('brand_id') is-invalid @enderror"
                                            id="brand_id"
                                            name="brand_id">
                                        <option value="">Selecciona una marca</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="supplier_name" class="form-label">Proveedor</label>
                                    <select class="form-select @error('supplier_name') is-invalid @enderror"
                                            id="supplier_name"
                                            name="supplier_name">
                                        <option value="">Selecciona un proveedor</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->name }}"
                                                {{ old('supplier_name') == $supplier->name ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('supplier_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label">Precio</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number"
                                               step="0.01"
                                               name="price"
                                               id="price"
                                               class="form-control @error('price') is-invalid @enderror"
                                               value="{{ old('price') }}"
                                               required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="current_stock" class="form-label">Stock Actual</label>
                                    <input type="number"
                                           name="current_stock"
                                           id="current_stock"
                                           class="form-control @error('current_stock') is-invalid @enderror"
                                           value="{{ old('current_stock', 0) }}"
                                           min="0">
                                    @error('current_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="minimum_stock" class="form-label">Stock Mínimo</label>
                                    <input type="number"
                                           name="minimum_stock"
                                           id="minimum_stock"
                                           class="form-control @error('minimum_stock') is-invalid @enderror"
                                           value="{{ old('minimum_stock', 0) }}"
                                           min="0">
                                    @error('minimum_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea name="description"
                                          id="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="4">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i>Guardar Producto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script>
        $(document).ready(function() {
            $('#description').summernote({
                placeholder: 'Agrega aquí la descripción del producto...',
                tabsize: 2,
                height: 200,
                lang: 'es-ES',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
    });
});
</script>
@endpush

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .select2-container--bootstrap-5 .select2-selection {
        height: calc(3.5rem + 2px);
        padding: 1rem 0.75rem;
        font-size: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .form-label {
        font-weight: 500;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>
@endpush
