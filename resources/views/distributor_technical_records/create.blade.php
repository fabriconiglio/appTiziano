<!-- resources/views/distributor_technical_records/create.blade.php -->
@extends('layouts.app')

@section('title', 'Nueva Ficha Técnica de Compra')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .product-row {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .remove-product {
            color: #dc3545;
            cursor: pointer;
        }
        .remove-product:hover {
            color: #c82333;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Nueva Ficha Técnica de Compra - {{ $distributorClient->name }} {{ $distributorClient->surname }}</h5>
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

                        <form action="{{ route('distributor-clients.technical-records.store', $distributorClient) }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="purchase_date" class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('purchase_date') is-invalid @enderror"
                                           id="purchase_date" name="purchase_date"
                                           value="{{ old('purchase_date', now()->format('Y-m-d\TH:i')) }}" required>
                                    @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="purchase_type" class="form-label">Tipo de Compra</label>
                                    <select class="form-select @error('purchase_type') is-invalid @enderror"
                                            id="purchase_type" name="purchase_type">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="al_por_mayor" {{ old('purchase_type') == 'al_por_mayor' ? 'selected' : '' }}>Al por Mayor</option>
                                        <option value="al_por_menor" {{ old('purchase_type') == 'al_por_menor' ? 'selected' : '' }}>Al por Menor</option>
                                        <option value="especial" {{ old('purchase_type') == 'especial' ? 'selected' : '' }}>Compra Especial</option>
                                    </select>
                                    @error('purchase_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="total_amount" class="form-label">Monto Total</label>
                                    <input type="number" step="0.01" class="form-control @error('total_amount') is-invalid @enderror"
                                           id="total_amount" name="total_amount"
                                           value="{{ old('total_amount') }}" placeholder="0.00">
                                    @error('total_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Método de Pago</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror"
                                            id="payment_method" name="payment_method">
                                        <option value="">Seleccionar método</option>
                                        <option value="efectivo" {{ old('payment_method') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="tarjeta" {{ old('payment_method') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                        <option value="transferencia" {{ old('payment_method') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                    @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Productos Comprados</label>
                                <div id="products-container">
                                    <!-- Los productos se agregarán dinámicamente aquí -->
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
                            </div>

                            <div class="mb-3">
                                <label for="observations" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror"
                                          id="observations" name="observations"
                                          rows="3">{{ old('observations') }}</textarea>
                                @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="next_purchase_notes" class="form-label">Notas para Próxima Compra</label>
                                <textarea class="form-control @error('next_purchase_notes') is-invalid @enderror"
                                          id="next_purchase_notes" name="next_purchase_notes"
                                          rows="2">{{ old('next_purchase_notes') }}</textarea>
                                @error('next_purchase_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('distributor-clients.show', $distributorClient) }}" class="btn btn-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Guardar Ficha Técnica de Compra
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

            let productIndex = 0;

            function addProductRow() {
                const productRow = `
                    <div class="product-row" data-index="${productIndex}">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Descripción del Producto</label>
                                <select class="form-select product-description-select" name="products_purchased[${productIndex}][product_id]" required>
                                    <option value="">Buscar por descripción...</option>
                                    @foreach($supplierInventories as $product)
                                        <option value="{{ $product->id }}" 
                                                data-stock="{{ $product->stock_quantity }}"
                                                data-product-name="{{ $product->product_name }}"
                                                data-description="{{ $product->description }}">
                                            {{ $product->description ?: $product->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Producto</label>
                                <input type="text" class="form-control product-name-display" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Marca</label>
                                <input type="text" class="form-control brand-display" readonly>
                            </div>
                            <div class="col-md-2">
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
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-sm remove-product" 
                                        onclick="removeProduct(${productIndex})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#products-container').append(productRow);
                
                // Inicializar Select2 para el nuevo select de descripción
                $(`.product-row[data-index="${productIndex}"] .product-description-select`).select2({
                    placeholder: 'Buscar por descripción...',
                    allowClear: true,
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
                                        text: item.description || item.product_name,
                                        stock: item.stock_quantity,
                                        productName: item.product_name,
                                        description: item.description,
                                        brand: item.distributor_brand ? item.distributor_brand.name : ''
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
                        return data.text;
                    }
                });
                
                // Event listeners para el nuevo producto
                $(`.product-row[data-index="${productIndex}"] .product-description-select`).on('select2:select', function(e) {
                    const data = e.params.data;
                    const stock = data.stock || 0;
                    const productName = data.productName || '';
                    const brand = data.brand || '';
                    
                    // Actualizar campos automáticamente
                    $(this).closest('.product-row').find('.stock-display').val(stock);
                    $(this).closest('.product-row').find('.product-name-display').val(productName);
                    $(this).closest('.product-row').find('.brand-display').val(brand);
                    
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
                });
                
                productIndex++;
            }

            $('#add-product').click(function() {
                addProductRow();
            });

            // Agregar el primer producto por defecto
            addProductRow();
        });

        function removeProduct(index) {
            $(`.product-row[data-index="${index}"]`).remove();
        }
    </script>
@endpush

@endsection 