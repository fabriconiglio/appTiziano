@extends('layouts.app')

@section('title', 'Editar Presupuesto - Cliente No Registrado')

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
        .product-row .col-md-2 {
            min-width: 120px;
        }
        .product-row .col-md-3 {
            min-width: 180px;
        }
        .product-row .col-md-5 {
            min-width: 250px;
        }
        .product-row .subtotal-display {
            min-width: 100px;
            width: 100%;
        }
        .product-row .d-flex.align-items-end {
            gap: 8px;
        }
        .product-row .btn-sm {
            height: 45px;
            font-size: 12px;
        }
        .summary-section {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .summary-total {
            font-size: 20px;
            font-weight: bold;
            border-top: 2px solid #dee2e6;
            padding-top: 10px;
            margin-top: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark">Editar Presupuesto - Cliente No Registrado</h5>
                        <a href="{{ route('distributor-quotation-no-clients.show', $distributorQuotationNoClient) }}" class="btn btn-secondary btn-sm">
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

                        <form action="{{ route('distributor-quotation-no-clients.update', $distributorQuotationNoClient) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <!-- Información del Cliente No Registrado -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-user"></i> Datos del Cliente
                                    </h6>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre del Cliente</label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" 
                                           value="{{ old('nombre', $distributorQuotationNoClient->nombre) }}"
                                           placeholder="Opcional">
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                           id="telefono" name="telefono" 
                                           value="{{ old('telefono', $distributorQuotationNoClient->telefono) }}"
                                           placeholder="Opcional">
                                    @error('telefono')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" 
                                           value="{{ old('email', $distributorQuotationNoClient->email) }}"
                                           placeholder="Opcional">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                                           id="direccion" name="direccion" 
                                           value="{{ old('direccion', $distributorQuotationNoClient->direccion) }}"
                                           placeholder="Opcional">
                                    @error('direccion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Información del Presupuesto -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label for="quotation_number" class="form-label">Número de Presupuesto *</label>
                                    <input type="text" class="form-control @error('quotation_number') is-invalid @enderror"
                                           id="quotation_number" name="quotation_number" 
                                           value="{{ old('quotation_number', $distributorQuotationNoClient->quotation_number) }}" required readonly>
                                    @error('quotation_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="quotation_date" class="form-label">Fecha del Presupuesto *</label>
                                    <input type="datetime-local" class="form-control @error('quotation_date') is-invalid @enderror"
                                           id="quotation_date" name="quotation_date" 
                                           value="{{ old('quotation_date', $distributorQuotationNoClient->quotation_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('quotation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="valid_until" class="form-label">Válido Hasta *</label>
                                    <input type="datetime-local" class="form-control @error('valid_until') is-invalid @enderror"
                                           id="valid_until" name="valid_until" 
                                           value="{{ old('valid_until', $distributorQuotationNoClient->valid_until->format('Y-m-d\TH:i')) }}" required>
                                    @error('valid_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="quotation_type" class="form-label">Tipo de Presupuesto *</label>
                                    <select class="form-select @error('quotation_type') is-invalid @enderror" 
                                            id="quotation_type" name="quotation_type" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="al_por_mayor" {{ old('quotation_type', $distributorQuotationNoClient->quotation_type) === 'al_por_mayor' ? 'selected' : '' }}>Al Por Mayor</option>
                                        <option value="al_por_menor" {{ old('quotation_type', $distributorQuotationNoClient->quotation_type) === 'al_por_menor' ? 'selected' : '' }}>Al Por Menor</option>
                                    </select>
                                    @error('quotation_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Configuración de Precios -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label for="tax_percentage" class="form-label">Porcentaje de IVA *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('tax_percentage') is-invalid @enderror"
                                               id="tax_percentage" name="tax_percentage" 
                                               value="{{ old('tax_percentage', $distributorQuotationNoClient->tax_percentage) }}" min="0" max="100" step="0.01" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    @error('tax_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="discount_percentage" class="form-label">Porcentaje de Descuento</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror"
                                               id="discount_percentage" name="discount_percentage" 
                                               value="{{ old('discount_percentage', $distributorQuotationNoClient->discount_percentage) }}" min="0" max="100" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    @error('discount_percentage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="payment_terms" class="form-label">Condiciones de Pago</label>
                                    <select class="form-select @error('payment_terms') is-invalid @enderror" 
                                            id="payment_terms" name="payment_terms">
                                        <option value="">Seleccionar método</option>
                                        <option value="efectivo" {{ old('payment_terms', $distributorQuotationNoClient->payment_terms) == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="tarjeta" {{ old('payment_terms', $distributorQuotationNoClient->payment_terms) == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                        <option value="transferencia" {{ old('payment_terms', $distributorQuotationNoClient->payment_terms) == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                        <option value="cheque" {{ old('payment_terms', $distributorQuotationNoClient->payment_terms) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    </select>
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="delivery_terms" class="form-label">Condiciones de Entrega</label>
                                    <input type="text" class="form-control @error('delivery_terms') is-invalid @enderror"
                                           id="delivery_terms" name="delivery_terms"
                                           value="{{ old('delivery_terms', $distributorQuotationNoClient->delivery_terms) }}" placeholder="Ej: Inmediata, 48hs">
                                    @error('delivery_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Productos del Presupuesto -->
                            <div class="mb-3">
                                <label class="form-label">
                                    Productos del Presupuesto 
                                    <span class="badge bg-primary" id="product-counter">{{ count($distributorQuotationNoClient->products_quoted) }}</span>
                                </label>
                                <div id="products-container">
                                    @if(count($distributorQuotationNoClient->products_quoted) > 0)
                                        @foreach($distributorQuotationNoClient->products_quoted as $index => $product)
                                            <div class="product-row" data-index="{{ $index }}">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <label class="form-label">Buscar Producto</label>
                                                        <select class="form-select product-description-select" name="products_quoted[{{ $index }}][product_id]" required>
                                                            <option value="">Buscar por nombre, descripción o marca...</option>
                                                            @php
                                                                $productInfo = App\Models\SupplierInventory::find($product['product_id']);
                                                            @endphp
                                                            @if($productInfo)
                                                                <option value="{{ $productInfo->id }}" selected>
                                                                    {{ $productInfo->product_name }} - {{ $productInfo->description }}
                                                                </option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Cantidad</label>
                                                        <input type="number" class="form-control quantity-input" 
                                                               name="products_quoted[{{ $index }}][quantity]" 
                                                               value="{{ $product['quantity'] }}" min="1" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Precio</label>
                                                        <input type="text" class="form-control price-display" readonly value="${{ number_format($product['price'], 2) }}">
                                                        <input type="hidden" class="price-value" name="products_quoted[{{ $index }}][price]" value="{{ $product['price'] }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Subtotal</label>
                                                        <div class="d-flex align-items-end">
                                                            <input type="text" class="form-control subtotal-display" readonly style="flex: 1; margin-right: 8px;" value="${{ number_format($product['quantity'] * $product['price'], 2) }}">
                                                            <button type="button" class="btn btn-outline-danger btn-sm remove-product" 
                                                                    data-index="{{ $index }}" style="height: 45px; min-width: 45px; flex-shrink: 0;">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center text-muted py-4" id="no-products-message">
                                            <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                            <p>No hay productos agregados. Haz clic en "Agregar Producto" para comenzar.</p>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-product">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>

                            <!-- Resumen -->
                            <div class="summary-section">
                                <h6 class="border-bottom pb-2 mb-3">Resumen del Presupuesto</h6>
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span id="subtotal-display">${{ number_format($distributorQuotationNoClient->subtotal, 2) }}</span>
                                </div>
                                <div class="summary-row">
                                    <span>IVA ({{ $distributorQuotationNoClient->tax_percentage }}%):</span>
                                    <span id="tax-display">${{ number_format($distributorQuotationNoClient->tax_amount, 2) }}</span>
                                </div>
                                <div class="summary-row">
                                    <span>Total con IVA:</span>
                                    <span id="total-with-tax-display">${{ number_format($distributorQuotationNoClient->total_amount, 2) }}</span>
                                </div>
                                <div class="summary-row">
                                    <span>Descuento ({{ $distributorQuotationNoClient->discount_percentage }}%):</span>
                                    <span id="discount-display">${{ number_format($distributorQuotationNoClient->discount_amount, 2) }}</span>
                                </div>
                                <div class="summary-row summary-total">
                                    <span>Total Final:</span>
                                    <span id="final-total-display">${{ number_format($distributorQuotationNoClient->final_amount, 2) }}</span>
                                </div>
                            </div>

                            <!-- Observaciones y Términos -->
                            <div class="row mb-4 mt-5">
                                <div class="col-md-6">
                                    <label for="observations" class="form-label">Observaciones</label>
                                    <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations" rows="3" placeholder="Observaciones adicionales">{{ old('observations', $distributorQuotationNoClient->observations) }}</textarea>
                                    @error('observations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="terms_conditions" class="form-label">Términos y Condiciones</label>
                                    <textarea class="form-control @error('terms_conditions') is-invalid @enderror" id="terms_conditions" name="terms_conditions" rows="3" placeholder="Términos y condiciones del presupuesto">{{ old('terms_conditions', $distributorQuotationNoClient->terms_conditions) }}</textarea>
                                    @error('terms_conditions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Fotos -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="photos" class="form-label">Fotos del Presupuesto (opcional)</label>
                                    <input type="file" class="form-control @error('photos.*') is-invalid @enderror" id="photos" name="photos[]" multiple accept="image/*">
                                    <small class="form-text text-muted">Puedes seleccionar múltiples imágenes. Máximo 2MB por imagen.</small>
                                    @error('photos.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    @if(!empty($distributorQuotationNoClient->photos))
                                        <div class="mt-3">
                                            <label class="form-label">Fotos Actuales:</label>
                                            <div class="row">
                                                @foreach($distributorQuotationNoClient->photos as $photo)
                                                    <div class="col-md-3 mb-3">
                                                        <img src="{{ Storage::url($photo) }}" 
                                                             alt="Foto del presupuesto" 
                                                             class="img-fluid rounded border"
                                                             style="max-height: 150px; width: 100%; object-fit: cover;">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-success btn-lg me-3">
                                        <i class="fas fa-save"></i> Actualizar Presupuesto
                                    </button>
                                    <a href="{{ route('distributor-quotation-no-clients.show', $distributorQuotationNoClient) }}" class="btn btn-secondary btn-lg">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#observations').summernote({
                placeholder: 'Agrega aquí las observaciones del presupuesto...',
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

            $('#terms_conditions').summernote({
                placeholder: 'Agrega aquí los términos y condiciones...',
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

            function addProductRow() {
                // Recalcular el índice basado en productos existentes
                const existingProducts = document.querySelectorAll('.product-row');
                const productIndex = existingProducts.length;

                const productRow = `
                    <div class="product-row" data-index="${productIndex}">
                        <div class="row">
                            <div class="col-md-5">
                                <label class="form-label">Buscar Producto</label>
                                <select class="form-select product-description-select" name="products_quoted[${productIndex}][product_id]" required>
                                    <option value="">Buscar por nombre, descripción o marca...</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control quantity-input" 
                                       name="products_quoted[${productIndex}][quantity]" 
                                       min="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Precio</label>
                                <input type="text" class="form-control price-display" readonly>
                                <input type="hidden" class="price-value" name="products_quoted[${productIndex}][price]">
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
                    
                    // Obtener precio del producto seleccionado
                    const productId = $(this).val();
                    if (productId) {
                        $.ajax({
                            url: '{{ route("api.supplier-inventories.get-product") }}',
                            method: 'GET',
                            data: { product_id: productId },
                            success: function(response) {
                                // Determinar el precio según el tipo de presupuesto
                                const quotationType = $('#quotation_type').val();
                                let price = 0;
                                
                                switch (quotationType) {
                                    case 'al_por_mayor':
                                        price = response.precio_mayor || 0;
                                        break;
                                    case 'al_por_menor':
                                        price = response.precio_menor || 0;
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
                });
                
                $(`.product-row[data-index="${productIndex}"] .quantity-input`).on('input', function() {
                    // Calcular subtotal
                    calculateSubtotal($(this).closest('.product-row'));
                });
                
                // Actualizar el contador de productos
                updateProductCounter();
            }

            $('#add-product').click(function() {
                addProductRow();
            });

            // Event listener para el botón de eliminar
            $(document).on('click', '.remove-product', function() {
                const index = $(this).data('index');
                const productRow = $(this).closest('.product-row');
                
                // Verificar que estamos eliminando la fila correcta
                if (productRow.length === 0) {
                    return;
                }
                
                // Eliminar la fila del producto
                productRow.remove();
                
                // Esperar a que se complete la eliminación del DOM
                setTimeout(function() {
                    // Recalcular índices después de eliminar
                    $('.product-row').each(function(newIndex) {
                        $(this).attr('data-index', newIndex);
                        
                        // Actualizar los nombres de los campos
                        $(this).find('select[name^="products_quoted["]').attr('name', `products_quoted[${newIndex}][product_id]`);
                        $(this).find('input[name^="products_quoted["]').each(function() {
                            const fieldName = $(this).attr('name').match(/\[([^\]]+)\]$/)[1];
                            $(this).attr('name', `products_quoted[${newIndex}][${fieldName}]`);
                        });
                        
                        // Actualizar el data-index del botón de eliminar
                        $(this).find('.remove-product').attr('data-index', newIndex);
                    });
                    
                    updateProductCounter();
                    calculateTotal();
                }, 10);
            });
            
            // Evento para recalcular precios cuando cambie el tipo de presupuesto
            $('#quotation_type').on('change', function() {
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
                                // Determinar el precio según el tipo de presupuesto
                                const quotationType = $('#quotation_type').val();
                                let price = 0;
                                
                                switch (quotationType) {
                                    case 'al_por_mayor':
                                        price = response.precio_mayor || 0;
                                        break;
                                    case 'al_por_menor':
                                        price = response.precio_menor || 0;
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
                
                // Actualizar resumen
                updateSummary(total);
            }

            // Función para actualizar el contador de productos
            function updateProductCounter() {
                const count = $('.product-row').length;
                $('#product-counter').text(count);
                
                // Mostrar/ocultar mensaje cuando no hay productos
                if (count === 0) {
                    $('#no-products-message').show();
                } else {
                    $('#no-products-message').hide();
                }
            }
            
            // Función para actualizar el resumen
            function updateSummary(subtotal) {
                const taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
                const discountPercentage = parseFloat($('#discount_percentage').val()) || 0;
                
                const taxAmount = subtotal * (taxPercentage / 100);
                const totalWithTax = subtotal + taxAmount;
                const discountAmount = totalWithTax * (discountPercentage / 100);
                const finalTotal = totalWithTax - discountAmount;
                
                $('#subtotal-display').text('$' + subtotal.toFixed(2));
                $('#tax-display').text('$' + taxAmount.toFixed(2));
                $('#total-with-tax-display').text('$' + totalWithTax.toFixed(2));
                $('#discount-display').text('$' + discountAmount.toFixed(2));
                $('#final-total-display').text('$' + finalTotal.toFixed(2));
            }
            
            // Eventos de cambio de porcentajes
            $('#tax_percentage, #discount_percentage').on('input', function() {
                let subtotal = 0;
                $('.product-row').each(function() {
                    const quantity = parseInt($(this).find('.quantity-input').val()) || 0;
                    const price = parseFloat($(this).find('.price-value').val()) || 0;
                    subtotal += quantity * price;
                });
                updateSummary(subtotal);
            });

            // Inicializar contador de productos
            updateProductCounter();
        });
    </script>
@endpush 