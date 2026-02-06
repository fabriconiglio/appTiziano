@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Editar Producto de Inventario</span>
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

                        <form action="{{ route('supplier-inventories.update', $supplierInventory) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="image_order" id="image_order">

                            <div class="row mb-4">
                                <h5>Información del Producto</h5>
                                <hr>
                                <div class="col-md-6 mb-3">
                                    <label for="product_name" class="form-label">Nombre del Producto *</label>
                                    <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name', $supplierInventory->product_name) }}" required>
                                    @error('product_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{--
                                <div class="col-md-6 mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $supplierInventory->sku) }}">
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
                                            <option value="{{ $category->id }}" {{ old('distributor_category_id', $supplierInventory->distributor_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
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
                                            <option value="{{ $brand->id }}" {{ old('distributor_brand_id', $supplierInventory->distributor_brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
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
                                            <option value="{{ $supplier->name }}" {{ old('supplier_name', $supplierInventory->supplier_name) == $supplier->name ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="precio_mayor" class="form-label">Precio al Mayor</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('precio_mayor') is-invalid @enderror" id="precio_mayor" name="precio_mayor" value="{{ old('precio_mayor', $supplierInventory->precio_mayor) }}">
                                        @error('precio_mayor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="precio_menor" class="form-label">Precio al Menor</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('precio_menor') is-invalid @enderror" id="precio_menor" name="precio_menor" value="{{ old('precio_menor', $supplierInventory->precio_menor) }}">
                                        @error('precio_menor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="costo" class="form-label">Costo</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control @error('costo') is-invalid @enderror" id="costo" name="costo" value="{{ old('costo', $supplierInventory->costo) }}">
                                        @error('costo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="stock_quantity" class="form-label">Cantidad en Inventario *</label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $supplierInventory->stock_quantity) }}" required>
                                    @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_restock_date" class="form-label">Fecha de Última Reposición</label>
                                    <input type="date" class="form-control @error('last_restock_date') is-invalid @enderror" id="last_restock_date" name="last_restock_date" value="{{ old('last_restock_date', $supplierInventory->last_restock_date ? $supplierInventory->last_restock_date->format('Y-m-d') : '') }}">
                                    @error('last_restock_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $supplierInventory->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Estado</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="available" {{ (old('status', $supplierInventory->status) == 'available') ? 'selected' : '' }}>Disponible</option>
                                        <option value="low_stock" {{ (old('status', $supplierInventory->status) == 'low_stock') ? 'selected' : '' }}>Bajo stock</option>
                                        <option value="out_of_stock" {{ (old('status', $supplierInventory->status) == 'out_of_stock') ? 'selected' : '' }}>Sin stock</option>
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <h5>Imágenes del Producto</h5>
                                <hr>
                                
                                @if($supplierInventory->images && count($supplierInventory->images) > 0)
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Imágenes actuales (arrastrar para reordenar)</label>
                                    <div id="current-images" class="d-flex flex-wrap gap-2">
                                        @foreach($supplierInventory->images as $index => $image)
                                        <div class="position-relative image-item" data-path="{{ $image }}" style="width: 120px;">
                                            <img src="{{ asset('storage/' . $image) }}" class="img-thumbnail" style="width: 100%; height: 100px; object-fit: cover; cursor: move;">
                                            @if($index === 0)
                                            <span class="badge bg-primary position-absolute top-0 start-0" style="font-size: 10px;">Principal</span>
                                            @endif
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 delete-image-btn" data-image="{{ $image }}" style="padding: 2px 6px; font-size: 10px;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <input type="hidden" name="delete_images[]" value="" class="delete-input" disabled>
                                        </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Haz clic en la X para eliminar una imagen. La primera imagen será la principal.</small>
                                </div>
                                @endif

                                <div class="col-md-12 mb-3">
                                    <label for="images" class="form-label">Agregar nuevas imágenes (máximo {{ 5 - count($supplierInventory->images ?? []) }} más)</label>
                                    <input type="file" class="form-control @error('images.*') is-invalid @enderror" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 2MB por imagen.</small>
                                    @error('images.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12 mb-3" id="new-image-preview-container" style="display: none;">
                                    <label class="form-label">Vista previa de nuevas imágenes</label>
                                    <div id="new-image-preview" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <h5>Tienda Nube</h5>
                                <hr>
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="publicar_tiendanube" name="publicar_tiendanube" value="1" {{ old('publicar_tiendanube', $supplierInventory->publicar_tiendanube) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="publicar_tiendanube">
                                            <i class="fas fa-cloud-upload-alt me-1"></i> Publicar en Tienda Nube
                                        </label>
                                        <small class="d-block text-muted">Marcar para sincronizar este producto con tu tienda online.</small>
                                    </div>
                                    @if($supplierInventory->tiendanube_product_id)
                                    <div class="mt-2">
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> Sincronizado con Tienda Nube</span>
                                        @if($supplierInventory->tiendanube_synced_at)
                                        <small class="text-muted ms-2">Última sincronización: {{ $supplierInventory->tiendanube_synced_at->format('d/m/Y H:i') }}</small>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <h5>Información Adicional</h5>
                                <hr>
                                <div class="col-md-12 mb-3">
                                    <label for="notes" class="form-label">Notas</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="6">{{ old('notes', $supplierInventory->notes) }}</textarea>
                                    @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('supplier-inventories.index') }}" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Actualizar Producto</button>
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
@push('styles')
<style>
    #current-images .image-item {
        transition: all 0.3s ease;
    }
    #current-images .image-item.dragging {
        opacity: 0.5;
    }
    #current-images .image-item.deleted {
        opacity: 0.3;
        filter: grayscale(100%);
    }
    #current-images .image-item.deleted .delete-image-btn {
        display: none;
    }
    #current-images .image-item.deleted::after {
        content: 'Eliminada';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(220, 53, 69, 0.9);
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
    }
</style>
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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

            // Sortable para reordenar imágenes
            const currentImagesEl = document.getElementById('current-images');
            if (currentImagesEl) {
                new Sortable(currentImagesEl, {
                    animation: 150,
                    ghostClass: 'dragging',
                    onEnd: function() {
                        updateImageOrder();
                        updatePrincipalBadge();
                    }
                });
            }

            // Eliminar imagen
            $(document).on('click', '.delete-image-btn', function() {
                const $item = $(this).closest('.image-item');
                const imagePath = $(this).data('image');
                
                if ($item.hasClass('deleted')) {
                    // Restaurar
                    $item.removeClass('deleted');
                    $item.find('.delete-input').prop('disabled', true).val('');
                } else {
                    // Marcar para eliminar
                    $item.addClass('deleted');
                    $item.find('.delete-input').prop('disabled', false).val(imagePath);
                }
                
                updateImageOrder();
                updatePrincipalBadge();
            });

            function updateImageOrder() {
                const order = [];
                $('#current-images .image-item:not(.deleted)').each(function() {
                    order.push($(this).data('path'));
                });
                $('#image_order').val(JSON.stringify(order));
            }

            function updatePrincipalBadge() {
                $('#current-images .badge.bg-primary').remove();
                const $firstVisible = $('#current-images .image-item:not(.deleted)').first();
                if ($firstVisible.length) {
                    $firstVisible.find('img').before('<span class="badge bg-primary position-absolute top-0 start-0" style="font-size: 10px;">Principal</span>');
                }
            }

            // Vista previa de nuevas imágenes
            const currentCount = {{ count($supplierInventory->images ?? []) }};
            const maxImages = 5;
            
            $('#images').on('change', function() {
                const preview = $('#new-image-preview');
                const container = $('#new-image-preview-container');
                preview.empty();
                
                const deletedCount = $('#current-images .image-item.deleted').length;
                const activeCount = currentCount - deletedCount;
                const maxNew = maxImages - activeCount;
                
                const files = this.files;
                if (files.length > maxNew) {
                    alert(`Solo puedes agregar ${maxNew} imagen(es) más. Máximo total: ${maxImages}`);
                    this.value = '';
                    container.hide();
                    return;
                }
                
                if (files.length > 0) {
                    container.show();
                    Array.from(files).forEach((file) => {
                        if (file.type.match('image.*')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const imgHtml = `
                                    <div class="position-relative" style="width: 100px; height: 100px;">
                                        <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                                        <span class="badge bg-info position-absolute top-0 start-0" style="font-size: 10px;">Nueva</span>
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

            // Inicializar orden
            updateImageOrder();
        });
    </script>
@endpush
