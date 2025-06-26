@extends('layouts.app')

@section('title', 'Editar Marca de Distribuidora')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <h2 class="fs-4 fw-bold">Editar Marca de Distribuidora</h2>
            </div>

            <div class="p-4">
                <form action="{{ route('distributor_brands.update', $distributorBrand) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $distributorBrand->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description', $distributorBrand->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="logo_url" class="form-label">URL del Logo</label>
                        <input type="url" class="form-control @error('logo_url') is-invalid @enderror"
                               id="logo_url" name="logo_url" value="{{ old('logo_url', $distributorBrand->logo_url) }}">
                        @error('logo_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Puedes ingresar una URL o subir una imagen desde tu computadora.</div>
                    </div>

                    <div class="mb-3">
                        <label for="logo_file" class="form-label">Subir Logo</label>
                        <input type="file" class="form-control @error('logo_file') is-invalid @enderror" id="logo_file" name="logo_file" accept="image/*">
                        @error('logo_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($distributorBrand->logo_url)
                            <div class="mt-2">
                                <strong>Logo actual:</strong><br>
                                <img src="{{ $distributorBrand->logo_url }}" alt="Logo actual" style="max-height: 80px;">
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="categories" class="form-label">Categorías</label>
                        <select class="form-select select2 @error('categories') is-invalid @enderror"
                                id="categories" name="categories[]" multiple>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ in_array($category->id, old('categories', $selectedCategories)) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('categories')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Puedes seleccionar múltiples categorías</div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="form-check-input" id="is_active"
                               name="is_active" value="1" {{ old('is_active', $distributorBrand->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Marca Activa</label>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Actualizar Marca de Distribuidora</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container .select2-selection--multiple {
            min-height: 38px;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#categories').select2({
                placeholder: 'Selecciona las categorías',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    },
                    removeAllItems: function() {
                        return "Remove all";
                    }
                },
                theme: 'bootstrap-5'
            });
        });
    </script>
@endpush 