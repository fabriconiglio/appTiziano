<!-- resources/views/distributor_technical_records/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Editar Ficha Técnica de Compra')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .product-row {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .remove-product {
            color: #dc3545;
            cursor: pointer;
        }
        .remove-product:hover {
            color: #c82333;
        }
        .photo-preview {
            max-width: 150px;
            max-height: 150px;
            margin: 5px;
            border-radius: 5px;
        }
        .photo-container {
            display: inline-block;
            position: relative;
            margin: 5px;
        }
        .delete-photo {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 12px;
            cursor: pointer;
        }
        /* Estilos para inputs más grandes */
        .product-row .form-control,
        .product-row .form-select {
            height: 45px;
            font-size: 12px;
            padding: 8px 12px;
        }
        .product-row .form-label {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #495057;
        }
        /* Hacer las columnas más anchas */
        .product-row .col-md-1 {
            min-width: 80px;
        }
        .product-row .col-md-2 {
            min-width: 120px;
        }
        .product-row .col-md-3 {
            min-width: 180px;
        }
        /* Hacer el input de descripción extra ancho */
        .product-row .description-display {
            min-width: 200px;
        }
        /* Hacer el input del subtotal más ancho */
        .product-row .subtotal-display {
            min-width: 100px;
            width: 100%;
        }
        /* Ajustar el contenedor del subtotal */
        .product-row .d-flex.align-items-end {
            gap: 8px;
        }
        /* Ajustar el botón de eliminar */
        .product-row .btn-sm {
            height: 45px;
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Editar Ficha Técnica de Compra - {{ $distributorClient->name }} {{ $distributorClient->surname }}</h5>
                        <a href="{{ route('distributor-clients.show', $distributorClient) }}" class="btn btn-secondary btn-sm">
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

                        <form action="{{ route('distributor-clients.technical-records.update', [$distributorClient, $distributorTechnicalRecord]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="purchase_date" class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('purchase_date') is-invalid @enderror"
                                           id="purchase_date" name="purchase_date"
                                           value="{{ old('purchase_date', $distributorTechnicalRecord->purchase_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="purchase_type" class="form-label">Tipo de Compra</label>
                                    <select class="form-select @error('purchase_type') is-invalid @enderror"
                                            id="purchase_type" name="purchase_type">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="al_por_mayor" {{ old('purchase_type', $distributorTechnicalRecord->purchase_type) == 'al_por_mayor' ? 'selected' : '' }}>Al por Mayor</option>
                                        <option value="al_por_menor" {{ old('purchase_type', $distributorTechnicalRecord->purchase_type) == 'al_por_menor' ? 'selected' : '' }}>Al por Menor</option>
                                        <option value="especial" {{ old('purchase_type', $distributorTechnicalRecord->purchase_type) == 'especial' ? 'selected' : '' }}>Compra Especial</option>
                                    </select>
                                    @error('purchase_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="total_amount" class="form-label">Monto Total</label>
                                    <input type="number" step="0.01" class="form-control @error('total_amount') is-invalid @enderror"
                                           id="total_amount" name="total_amount"
                                           value="{{ old('total_amount', $distributorTechnicalRecord->total_amount) }}" placeholder="0.00" readonly>
                                    @error('total_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="advance_payment" class="form-label">Entrega Anticipada de Dinero</label>
                                    <input type="number" step="0.01" class="form-control @error('advance_payment') is-invalid @enderror"
                                           id="advance_payment" name="advance_payment"
                                           value="{{ old('advance_payment', $distributorTechnicalRecord->advance_payment ?? 0) }}" placeholder="0.00">
                                    @error('advance_payment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label for="final_amount" class="form-label">Monto Final a Pagar</label>
                                    <input type="text" class="form-control" id="final_amount" readonly 
                                           value="${{ number_format($distributorTechnicalRecord->final_amount, 2) }}" style="background-color: #e9ecef;">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Método de Pago</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror"
                                            id="payment_method" name="payment_method">
                                        <option value="">Seleccionar método</option>
                                        <option value="efectivo" {{ old('payment_method', $distributorTechnicalRecord->payment_method) == 'selected' : '' }}>Efectivo</option>
                                        <option value="tarjeta" {{ old('payment_method', $distributorTechnicalRecord->payment_method) == 'selected' : '' }}>Tarjeta</option>
                                        <option value="transferencia" {{ old('payment_method', $distributorTechnicalRecord->payment_method) == 'selected' : '' }}>Transferencia</option>
                                        <option value="cheque" {{ old('payment_method', $distributorTechnicalRecord->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                    @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Productos Comprados</label>
                                <!-- Debug: {{ count($distributorTechnicalRecord->products_purchased ?? []) }} productos -->
                                <div id="products-container">
                                    @if(!empty($distributorTechnicalRecord->products_purchased))
                                        @foreach($distributorTechnicalRecord->products_purchased as $index => $productData)
                                            <div class="product-row" data-index="{{ $index }}">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Buscar Producto</label>
                                                        <select class="form-select product-description-select" name="products_purchased[{{ $index }}][product_id]" required>
                                                            <option value="">Buscar por nombre, descripción o marca...</option>
                                                            <option value="{{ $productData['product_id'] }}" selected>
                                                                {{ $supplierInventories->firstWhere('id', $productData['product_id'])->product_name ?? 'Producto no encontrado' }} - {{ $supplierInventories->firstWhere('id', $productData['product_id'])->description ?? '' }} - {{ $supplierInventories->firstWhere('id', $productData['product_id'])->distributorBrand->name ?? 'Sin marca' }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label">Cantidad</label>
                                                        <input type="number" class="form-control quantity-input" 
                                                               name="products_purchased[{{ $index }}][quantity]" 
                                                               value="{{ $productData['quantity'] }}"
                                                               min="1" required>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label">Stock</label>
                                                        <input type="text" class="form-control stock-display" readonly 
                                                               value="{{ $supplierInventories->firstWhere('id', $productData['product_id'])->stock_quantity ?? '' }}">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label">Precio</label>
                                                        <input type="text" class="form-control price-display" readonly>
                                                        <input type="hidden" class="price-value" name="products_purchased[{{ $index }}][price]">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Subtotal</label>
                                                        <div class="d-flex align-items-end">
                                                            <input type="text" class="form-control subtotal-display" readonly style="flex: 1; margin-right: 8px;">
                                                            <button type="button" class="btn btn-outline-danger btn-sm remove-product" 
                                                                    data-index="{{ $index }}" style="height: 45px; min-width: 45px; flex-shrink: 0;">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-product">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>

                            <div class="mb-3">
                                <label for="photos" class="form-label">Fotos</label>
                                <input type="file" class="form-control @error('photos') is-invalid @enderror"
                                       id="photos" name="photos[]" multiple accept="image/*">
                                <div class="form-text">Puedes seleccionar múltiples fotos. Máximo 2MB por imagen.</div>
                                @error('photos')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                @if(!empty($distributorTechnicalRecord->photos))
                                    <div class="mt-3">
                                        <label class="form-label">Fotos Actuales</label>
                                        <div>
                                            @foreach($distributorTechnicalRecord->photos as $photo)
                                                <div class="photo-container">
                                                    <img src="{{ Storage::url($photo) }}" alt="Foto" class="photo-preview">
                                                    <button type="button" class="delete-photo" 
                                                            data-photo="{{ $photo }}"
                                                            onclick="deletePhoto('{{ $photo }}')">
                                                        ×
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label for="observations" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror"
                                          id="observations" name="observations"
                                          rows="3">{{ old('observations', $distributorTechnicalRecord->observations) }}</textarea>
                                @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="next_purchase_notes" class="form-label">Notas para Próxima Compra</label>
                                <textarea class="form-control @error('next_purchase_notes') is-invalid @enderror"
                                          id="next_purchase_notes" name="next_purchase_notes"
                                          rows="2">{{ old('next_purchase_notes', $distributorTechnicalRecord->next_purchase_notes) }}</textarea>
                                @error('next_purchase_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('distributor-clients.show', $distributorClient) }}" class="btn btn-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Actualizar Ficha Técnica de Compra
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#observations').summernote({
                placeholder: 'Agrega aquí las observaciones de la compra...',
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

                            // Inicializar Select2 para productos existentes con búsqueda por nombre y marca
                $('.product-description-select').select2({
                    placeholder: 'Buscar por nombre y marca...',
                    allowClear: true,
                    language: {
                        errorLoading: function() {
                            return 'No se pudieron cargar los resultados.';
                        },
                        inputTooShort: function() {
                            return 'Por favor ingresa más caracteres.';
                        },
                        loadingMore: function() {
                            return 'Cargando más resultados…';
                        },
                        maximumSelected: function() {
                            return 'Solo puedes seleccionar un elemento.';
                        },
                        noResults: function() {
                            return 'No se encontraron resultados.';
                        },
                        searching: function() {
                            return 'Buscando…';
                        }
                    },
                    ajax: {
                        url: '{{ route("api.supplier-inventories.search") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                page: params.page
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.map(function(item) {
                                    // Usar el display_text del backend si está disponible, sino construirlo
                                    let displayText = item.display_text;
                                    if (!displayText) {
                                        const parts = [item.product_name];
                                        if (item.description && item.description.trim()) {
                                            parts.push(item.description.trim());
                                        }
                                        if (item.brand && item.brand.trim()) {
                                            parts.push(item.brand.trim());
                                        }
                                        displayText = parts.join(' - ');
                                    }
                                    
                                    return {
                                        id: item.id,
                                        text: displayText,
                                        stock: item.stock_quantity,
                                        productName: item.product_name,
                                        description: item.description,
                                        brand: item.brand || ''
                                    };
                                }),
                                pagination: {
                                    more: false
                                }
                            };
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (data.loading) return data.text;
                        if (!data.id) return data.text;
                        return $(`<span>${data.text}</span>`);
                    },
                    templateSelection: function(data) {
                        if (!data.id) return data.text;
                        
                        // Siempre construir el texto usando los datos originales, ignorando el text que recibe
                        if (data.productName) {
                            const parts = [data.productName];
                            if (data.description && data.description.trim()) {
                                parts.push(data.description.trim());
                            }
                            if (data.brand && data.brand.trim()) {
                                parts.push(data.brand.trim());
                            }
                            return parts.join(' - ');
                        }
                        
                        return data.text;
                    }
                });

            // Event listeners para productos existentes
            $('.product-description-select').on('select2:select', function(e) {
                const data = e.params.data;
                const stock = data.stock || 0;
                const productName = data.productName || '';
                const brand = data.brand || '';
                const description = data.description || '';
                
                // Actualizar campos automáticamente
                $(this).closest('.product-row').find('.stock-display').val(stock);
                
                // Limpiar cantidad si no hay stock
                if (stock <= 0) {
                    $(this).closest('.product-row').find('.quantity-input').val('').addClass('is-invalid');
                } else {
                    $(this).closest('.product-row').find('.quantity-input').removeClass('is-invalid');
                }
            });

            $('.quantity-input').on('input', function() {
                const quantity = parseInt($(this).val()) || 0;
                const stock = parseInt($(this).closest('.product-row').find('.stock-display').val()) || 0;
                
                if (quantity > stock) {
                    $(this).addClass('is-invalid');
                    $(this).closest('.product-row').find('.stock-display').addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).closest('.product-row').find('.stock-display').removeClass('is-invalid');
                }
            });

            // Inicializar stock display para productos existentes
            $('.product-select').each(function() {
                const selectedOption = $(this).find('option:selected');
                const stock = selectedOption.data('stock') || 0;
                $(this).closest('.product-row').find('.stock-display').val(stock);
            });

            let productIndex = parseInt('{{ count($distributorTechnicalRecord->products_purchased ?? []) }}');

            function addProductRow() {
                const productRow = `
                    <div class="product-row" data-index="${productIndex}">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Buscar Producto</label>
                                <select class="form-select product-description-select" name="products_purchased[${productIndex}][product_id]" required>
                                    <option value="">Buscar por nombre, descripción o marca...</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control quantity-input" 
                                       name="products_purchased[${productIndex}][quantity]" 
                                       min="1" required>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Stock</label>
                                <input type="text" class="form-control stock-display" readonly>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">Precio</label>
                                <input type="text" class="form-control price-display" readonly>
                                <input type="hidden" class="price-value" name="products_purchased[${productIndex}][price]">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Subtotal</label>
                                <div class="d-flex align-items-end">
                                    <input type="text" class="form-control subtotal-display" readonly style="flex: 1; margin-right: 8px;">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-product" 
                                            data-index="${productIndex}" style="height: 45px; min-width: 45px; flex-shrink: 0;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#products-container').append(productRow);
                
                // Inicializar Select2 para el nuevo select de búsqueda
                $(`.product-row[data-index="${productIndex}"] .product-description-select`).select2({
                    placeholder: 'Buscar por nombre y marca...',
                    allowClear: true,
                    language: {
                        errorLoading: function() {
                            return 'No se pudieron cargar los resultados.';
                        },
                        inputTooShort: function() {
                            return 'Por favor ingresa más caracteres.';
                        },
                        loadingMore: function() {
                            return 'Cargando más resultados…';
                        },
                        maximumSelected: function() {
                            return 'Solo puedes seleccionar un elemento.';
                        },
                        noResults: function() {
                            return 'No se encontraron resultados.';
                        },
                        searching: function() {
                            return 'Buscando…';
                        }
                    },
                    ajax: {
                        url: '{{ route("api.supplier-inventories.search") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                page: params.page
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.map(function(item) {
                                    return {
                                        id: item.id,
                                        text: item.display_text || item.product_name,
                                        stock: item.stock_quantity,
                                        productName: item.product_name,
                                        description: item.description,
                                        brand: item.brand || ''
                                    };
                                }),
                                pagination: {
                                    more: false
                                }
                            };
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (data.loading) return data.text;
                        if (!data.id) return data.text;
                        return $(`<span>${data.text}</span>`);
                    },
                    templateSelection: function(data) {
                        if (!data.id) return data.text;
                        
                        // Siempre construir el texto usando los datos originales, ignorando el text que recibe
                        if (data.productName) {
                            const parts = [data.productName];
                            if (data.description && data.description.trim()) {
                                parts.push(data.description.trim());
                            }
                            if (data.brand && data.brand.trim()) {
                                parts.push(data.brand.trim());
                            }
                            return parts.join(' - ');
                        }
                        
                        return data.text;
                    }
                });
                
                // Event listeners para el nuevo producto
                $(`.product-row[data-index="${productIndex}"] .product-description-select`).on('select2:select', function(e) {
                    const data = e.params.data;
                    const stock = data.stock || 0;
                    const productName = data.productName || '';
                    const brand = data.brand || '';
                    const description = data.description || '';
                    
                    // Actualizar campos automáticamente
                    $(this).closest('.product-row').find('.stock-display').val(stock);
                    $(this).closest('.product-row').find('.product-name-display').val(productName + (brand ? ' - ' + brand : ''));
                    $(this).closest('.product-row').find('.brand-display').val(brand);
                    $(this).closest('.product-row').find('.description-display').val(description);
                    
                    // Obtener precio del producto seleccionado
                    const productId = $(this).val();
                    if (productId) {
                        $.ajax({
                            url: '{{ route("api.supplier-inventories.get-product") }}',
                            method: 'GET',
                            data: { product_id: productId },
                            success: function(response) {
                                // Determinar el precio según el tipo de compra
                                const purchaseType = $('#purchase_type').val();
                                let price = 0;
                                
                                switch (purchaseType) {
                                    case 'al_por_mayor':
                                        price = response.precio_mayor || 0;
                                        break;
                                    case 'al_por_menor':
                                        price = response.precio_menor || 0;
                                        break;
                                    case 'especial':
                                        price = response.precio_menor || response.precio_mayor || 0;
                                        break;
                                    default:
                                        price = response.precio_menor || response.precio_mayor || 0;
                                        break;
                                }
                                
                                const priceDisplay = price > 0 ? '$' + parseFloat(price).toFixed(2) : 'N/A';
                                
                                $(this).closest('.product-row').find('.price-display').val(priceDisplay);
                                $(this).closest('.product-row').find('.price-value').val(price);
                                
                                // Calcular subtotal si hay cantidad
                                const quantity = parseInt($(this).closest('.product-row').find('.quantity-input').val()) || 0;
                                if (quantity > 0) {
                                    calculateSubtotal($(this).closest('.product-row'));
                                }
                            }.bind(this),
                            error: function() {
                                $(this).closest('.product-row').find('.price-display').val('Error');
                                $(this).closest('.product-row').find('.price-value').val(0);
                            }
                        });
                    }
                    
                    // Limpiar cantidad si no hay stock
                    if (stock <= 0) {
                        $(this).closest('.product-row').find('.quantity-input').val('').addClass('is-invalid');
                    } else {
                        $(this).closest('.product-row').find('.quantity-input').removeClass('is-invalid');
                    }
                });
                
                $(`.product-row[data-index="${productIndex}"] .quantity-input`).on('input', function() {
                    const quantity = parseInt($(this).val()) || 0;
                    const stock = parseInt($(this).closest('.product-row').find('.stock-display').val()) || 0;
                    
                    if (quantity > stock) {
                        $(this).addClass('is-invalid');
                        $(this).closest('.product-row').find('.stock-display').addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).closest('.product-row').find('.stock-display').removeClass('is-invalid');
                    }
                    
                    // Calcular subtotal
                    calculateSubtotal($(this).closest('.product-row'));
                });
                
                productIndex++;
            }

            $('#add-product').click(function() {
                addProductRow();
            });
            
            // Evento para eliminar productos
            $(document).on('click', '.remove-product', function() {
                const index = $(this).data('index');
                $(`.product-row[data-index="${index}"]`).remove();
                calculateTotal();
            });

            // Inicializar eventos para productos existentes
            $('.product-row').each(function() {
                const productRow = $(this);
                const productId = productRow.find('.product-description-select').val();
                
                if (productId) {
                    // Obtener precio del producto existente
                    $.ajax({
                        url: '{{ route("api.supplier-inventories.get-product") }}',
                        method: 'GET',
                        data: { product_id: productId },
                        success: function(response) {
                            const price = response.precio_mayor || response.precio_menor || 0;
                            const priceDisplay = price > 0 ? '$' + parseFloat(price).toFixed(2) : 'N/A';
                            
                            productRow.find('.price-display').val(priceDisplay);
                            productRow.find('.price-value').val(price);
                            
                            // Calcular subtotal inicial
                            calculateSubtotal(productRow);
                        },
                        error: function() {
                            productRow.find('.price-display').val('Error');
                            productRow.find('.price-value').val(0);
                        }
                    });
                }
                
                // Evento para cambio de cantidad en productos existentes
                productRow.find('.quantity-input').on('input', function() {
                    calculateSubtotal(productRow);
                });
            });
            
            // Calcular total inicial
            calculateTotal();
            
            // Evento para recalcular precios cuando cambie el tipo de compra
            $('#purchase_type').on('change', function() {
                $('.product-row').each(function() {
                    const productRow = $(this);
                    const productId = productRow.find('.product-description-select').val();
                    
                    if (productId) {
                        // Recalcular precio del producto
                        $.ajax({
                            url: '{{ route("api.supplier-inventories.get-product") }}',
                            method: 'GET',
                            data: { product_id: productId },
                            success: function(response) {
                                // Determinar el precio según el tipo de compra
                                const purchaseType = $('#purchase_type').val();
                                let price = 0;
                                
                                switch (purchaseType) {
                                    case 'al_por_mayor':
                                        price = response.precio_mayor || 0;
                                        break;
                                    case 'al_por_menor':
                                        price = response.precio_menor || 0;
                                        break;
                                    case 'especial':
                                        price = response.precio_menor || response.precio_mayor || 0;
                                        break;
                                    default:
                                        price = response.precio_menor || response.precio_mayor || 0;
                                        break;
                                }
                                
                                const priceDisplay = price > 0 ? '$' + parseFloat(price).toFixed(2) : 'N/A';
                                
                                productRow.find('.price-display').val(priceDisplay);
                                productRow.find('.price-value').val(price);
                                
                                // Recalcular subtotal
                                calculateSubtotal(productRow);
                            },
                            error: function() {
                                productRow.find('.price-display').val('Error');
                                productRow.find('.price-value').val(0);
                            }
                        });
                    }
                });
            });
        });


        
        // Función para calcular subtotal de una fila
        function calculateSubtotal(productRow) {
            const quantity = parseInt(productRow.find('.quantity-input').val()) || 0;
            const price = parseFloat(productRow.find('.price-value').val()) || 0;
            const subtotal = quantity * price;
            
            productRow.find('.subtotal-display').val(subtotal > 0 ? '$' + subtotal.toFixed(2) : '$0.00');
            
            // Calcular total general
            calculateTotal();
        }
        
        // Función para calcular total general
        function calculateTotal() {
            let total = 0;
            $('.product-row').each(function() {
                const quantity = parseInt($(this).find('.quantity-input').val()) || 0;
                const price = parseFloat($(this).find('.price-value').val()) || 0;
                total += quantity * price;
            });
            
            $('#total_amount').val(total.toFixed(2));
            calculateFinalAmount();
        }

        // Función para calcular monto final
        function calculateFinalAmount() {
            const total = parseFloat($('#total_amount').val()) || 0;
            const advance = parseFloat($('#advance_payment').val()) || 0;
            const finalAmount = Math.max(0, total - advance);
            
            $('#final_amount').val('$' + finalAmount.toFixed(2));
        }

        // Evento para recalcular monto final cuando cambie el adelanto
        $('#advance_payment').on('input', function() {
            calculateFinalAmount();
        });

        function deletePhoto(photo) {
            if (confirm('¿Estás seguro de que quieres eliminar esta foto?')) {
                $.ajax({
                    url: '{{ route("distributor-clients.technical-records.delete-photo", [$distributorClient, $distributorTechnicalRecord]) }}',
                    method: 'POST',
                    data: {
                        photo: photo,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $(`.photo-container img[src="{{ Storage::url('') }}${photo}"]`).parent().remove();
                        }
                    },
                    error: function() {
                        alert('Error al eliminar la foto');
                    }
                });
            }
        }
    </script>
@endpush

@endsection 