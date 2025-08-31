@extends('layouts.app')

@section('title', isset($product) ? 'Editar Producto' : 'Crear Producto')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3>{{ isset($product) ? 'Editar Producto' : 'Crear Producto' }}</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}">
                        @csrf
                        @if(isset($product))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $product->name ?? '') }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{--
                            <div class="col-md-6 mb-3">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text"
                                       class="form-control @error('sku') is-invalid @enderror"
                                       id="sku"
                                       name="sku"
                                       value="{{ old('sku', $product->sku ?? '') }}">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            --}}
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
                                            {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
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
                                            {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
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
                                           class="form-control @error('price') is-invalid @enderror"
                                           id="price"
                                           name="price"
                                           step="0.01"
                                           value="{{ old('price', $product->price ?? '') }}"
                                           required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="current_stock" class="form-label">Stock Actual</label>
                                <input type="number"
                                       class="form-control @error('current_stock') is-invalid @enderror"
                                       id="current_stock"
                                       name="current_stock"
                                       value="{{ old('current_stock', $product->current_stock ?? '0') }}"
                                       min="0">
                                @error('current_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="minimum_stock" class="form-label">Stock Mínimo</label>
                                <input type="number"
                                       class="form-control @error('minimum_stock') is-invalid @enderror"
                                       id="minimum_stock"
                                       name="minimum_stock"
                                       value="{{ old('minimum_stock', $product->minimum_stock ?? '0') }}"
                                       min="0">
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3">{{ old('description', $product->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas {{ isset($product) ? 'fa-edit' : 'fa-plus' }} me-2"></i>
                                {{ isset($product) ? 'Actualizar' : 'Crear' }} Producto
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Select2 para categorías y marcas
    $('#category_id, #brand_id').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Cargar marcas cuando cambia la categoría
    $('#category_id').on('change', function() {
        let categoryId = $(this).val();
        if (categoryId) {
            $.get(`/products/brands-by-category/${categoryId}`, function(brands) {
                let brandSelect = $('#brand_id');
                brandSelect.empty();
                brandSelect.append('<option value="">Selecciona una marca</option>');
                brands.forEach(function(brand) {
                    brandSelect.append(`<option value="${brand.id}">${brand.name}</option>`);
                });
                brandSelect.trigger('change');
            });
        }
    });
});
</script>
@endpush

@push('styles')
<style>
    .select2-container--bootstrap-5 .select2-selection {
        height: calc(3.5rem + 2px);
        padding: 1rem 0.75rem;
        font-size: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
</style>
@endpush
