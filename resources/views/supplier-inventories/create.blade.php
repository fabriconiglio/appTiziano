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

                        <form action="{{ route('supplier-inventories.store') }}" method="POST" enctype="multipart/form-data">
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

                                {{--
                                <div class="col-md-6 mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}">
                                    @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                --}}

                                <div class="col-md-6 mb-3">
                                    <label for="distributor_category_id" class="form-label">Categoría Distribuidora</label>
                                    <select class="form-select @error('distributor_category_id') is-invalid @enderror" id="distributor_category_id" name="distributor_category_id">
                                        <option value="">Selecciona una categoría</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('distributor_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('distributor_category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="distributor_brand_id" class="form-label">Marca Distribuidora</label>
                                    <select class="form-select @error('distributor_brand_id') is-invalid @enderror" id="distributor_brand_id" name="distributor_brand_id">
                                        <option value="">Selecciona una marca</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('distributor_brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('distributor_brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="supplier_name" class="form-label">Proveedor</label>
                                    <select class="form-select @error('supplier_name') is-invalid @enderror" id="supplier_name" name="supplier_name">
                                        <option value="">Selecciona un proveedor</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->name }}" {{ old('supplier_name') == $supplier->name ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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

                                <div class="col-md-6 mb-3">
                                    <label for="precio_mayor" class="form-label">Precio al Mayor</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('precio_mayor') is-invalid @enderror" id="precio_mayor" name="precio_mayor" value="{{ old('precio_mayor') }}">
                                        @error('precio_mayor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="precio_menor" class="form-label">Precio al Menor</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('precio_menor') is-invalid @enderror" id="precio_menor" name="precio_menor" value="{{ old('precio_menor') }}">
                                        @error('precio_menor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="costo" class="form-label">Costo</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('costo') is-invalid @enderror" id="costo" name="costo" value="{{ old('costo') }}">
                                        @error('costo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
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
                                <h5>Imágenes del Producto</h5>
                                <hr>
                                <div class="col-md-12 mb-3">
                                    <label for="images" class="form-label">Imágenes (máximo 5)</label>
                                    <input type="file" class="form-control @error('images.*') is-invalid @enderror" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 2MB por imagen.</small>
                                    @error('images.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3" id="image-preview-container" style="display: none;">
                                    <label class="form-label">Vista previa</label>
                                    <div id="image-preview" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <h5>Tienda Nube</h5>
                                <hr>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="publicar_tiendanube" name="publicar_tiendanube" value="1" {{ old('publicar_tiendanube') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="publicar_tiendanube">
                                            <i class="fas fa-cloud-upload-alt me-1"></i> Publicar en Tienda Nube
                                        </label>
                                        <small class="d-block text-muted">Marcar para sincronizar este producto con tu tienda online.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <h5>Información Adicional</h5>
                                <hr>
                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">Notas</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="6">{{ old('notes') }}</textarea>
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

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#notes').summernote({
                placeholder: 'Agrega aquí información adicional como en un blog de notas...',
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

            // Vista previa de imágenes
            $('#images').on('change', function() {
                const preview = $('#image-preview');
                const container = $('#image-preview-container');
                preview.empty();
                
                const files = this.files;
                if (files.length > 5) {
                    alert('Solo puedes subir un máximo de 5 imágenes');
                    this.value = '';
                    container.hide();
                    return;
                }
                
                if (files.length > 0) {
                    container.show();
                    Array.from(files).forEach((file, index) => {
                        if (file.type.match('image.*')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const imgHtml = `
                                    <div class="position-relative" style="width: 100px; height: 100px;">
                                        <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                        ${index === 0 ? '<span class="badge bg-primary position-absolute top-0 start-0" style="font-size: 10px;">Principal</span>' : ''}
                                    </div>
                                `;
                                preview.append(imgHtml);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                } else {
                    container.hide();
                }
            });
        });
    </script>
@endpush
