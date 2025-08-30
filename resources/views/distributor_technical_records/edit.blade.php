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

                        <!-- Información de la Ficha Técnica a Editar -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="alert-heading mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Información de la Ficha Técnica a Editar
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>ID de Ficha:</strong><br>
                                            <span class="badge bg-primary">#{{ $distributorTechnicalRecord->id }}</span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Fecha Original:</strong><br>
                                            {{ $distributorTechnicalRecord->purchase_date->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Monto Total Original:</strong><br>
                                            ${{ number_format($distributorTechnicalRecord->total_amount, 2) }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Monto Final Original:</strong><br>
                                            ${{ number_format($distributorTechnicalRecord->final_amount, 2) }}
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <strong>Productos Originales:</strong><br>
                                            @if(!empty($distributorTechnicalRecord->products_purchased))
                                                @foreach($distributorTechnicalRecord->products_purchased as $productData)
                                                    @php
                                                        $product = $supplierInventories->firstWhere('id', $productData['product_id']);
                                                    @endphp
                                                    • {{ $product->product_name ?? 'Producto no encontrado' }} - Cantidad: {{ $productData['quantity'] }} - Precio: ${{ number_format($productData['price'] ?? 0, 2) }}<br>
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Observaciones Originales:</strong><br>
                                            {{ $distributorTechnicalRecord->observations ?: 'Sin observaciones' }}
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <strong>Cuenta Corriente Original:</strong><br>
                                            @if($distributorTechnicalRecord->final_amount != $distributorTechnicalRecord->total_amount)
                                                <span class="badge bg-info">
                                                    Ajuste aplicado: ${{ number_format(abs($distributorTechnicalRecord->total_amount - $distributorTechnicalRecord->final_amount), 2) }}
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    @if($distributorTechnicalRecord->total_amount > $distributorTechnicalRecord->final_amount)
                                                        El cliente tenía crédito que se aplicó a esta compra
                                                    @else
                                                        Se agregó deuda de cuenta corriente a esta compra
                                                    @endif
                                                </small>
                                            @else
                                                <span class="badge bg-secondary">Sin ajuste de cuenta corriente</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Método de Pago Original:</strong><br>
                                            {{ $distributorTechnicalRecord->payment_method ? ucfirst($distributorTechnicalRecord->payment_method) : 'No especificado' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                           value="{{ old('total_amount', $distributorTechnicalRecord->total_amount) }}" placeholder="0.00" readonly>
                                    @error('total_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="final_amount" class="form-label">Monto Final a Pagar</label>
                                    <input type="text" class="form-control" id="final_amount" readonly 
                                           value="${{ number_format($distributorTechnicalRecord->final_amount, 2) }}" style="background-color: #e9ecef;">
                                </div>
                            </div>

                            <!-- Información de Cuenta Corriente -->
                            <div class="card mb-3 border-info">
                                <div class="card-header bg-info">
                                    <h6 class="mb-0 text-dark">
                                        <i class="fas fa-calculator me-2"></i>
                                        Información de Cuenta Corriente
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Saldo Actual</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="text" class="form-control" id="current_balance" 
                                                       value="{{ number_format($distributorClient->getCurrentBalance(), 2, ',', '.') }}" 
                                                       readonly style="background-color: #e9ecef;">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Estado</label>
                                            <div class="mt-2">
                                                @if($distributorClient->getCurrentBalance() > 0)
                                                    <span class="badge bg-danger fs-6">Con Deuda</span>
                                                @elseif($distributorClient->getCurrentBalance() < 0)
                                                    <span class="badge bg-success fs-6">A Favor</span>
                                                @else
                                                    <span class="badge bg-secondary fs-6">Al Día</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Ajuste Automático</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="text" class="form-control" id="balance_adjustment" 
                                                       value="0,00" readonly style="background-color: #e9ecef;">
                                            </div>
                                            <small class="form-text text-muted">
                                                @if($distributorClient->getCurrentBalance() > 0)
                                                    Se sumará a la compra
                                                @elseif($distributorClient->getCurrentBalance() < 0)
                                                    Se descontará de la compra
                                                @else
                                                    Sin ajuste necesario
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Opción para decidir si registrar en cuenta corriente -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="use_current_account" name="use_current_account" value="1" checked>
                                                <label class="form-check-label" for="use_current_account">
                                                    <strong>Registrar en cuenta corriente</strong>
                                                </label>
                                                <small class="form-text text-muted d-block">
                                                    Marca esta opción si quieres que esta compra se registre en la cuenta corriente del cliente. 
                                                    Si la desmarcas, la compra se registrará como pagada completamente sin afectar la cuenta corriente.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Campo oculto para el balance_adjustment -->
                            <input type="hidden" id="balance_adjustment_hidden" name="balance_adjustment" value="0">
                            
                            <!-- Campo oculto para asegurar que use_current_account siempre se envíe -->
                            <input type="hidden" name="use_current_account_hidden" id="use_current_account_hidden" value="1">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Método de Pago</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror"
                                            id="payment_method" name="payment_method">
                                        <option value="">Seleccionar método</option>
                                        <option value="efectivo" {{ old('payment_method', $distributorTechnicalRecord->payment_method) == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="tarjeta" {{ old('payment_method', $distributorTechnicalRecord->payment_method) == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                        <option value="transferencia" {{ old('payment_method', $distributorTechnicalRecord->payment_method) == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
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
                                                        <input type="text" class="form-control price-display" readonly 
                                                               value="${{ number_format($productData['price'] ?? 0, 2) }}">
                                                        <input type="hidden" class="price-value" name="products_purchased[{{ $index }}][price]" 
                                                               value="{{ $productData['price'] ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Subtotal</label>
                                                        <div class="d-flex align-items-end">
                                                            <input type="text" class="form-control subtotal-display" readonly style="flex: 1; margin-right: 8px;"
                                                                   value="${{ number_format($productData['quantity'] * ($productData['price'] ?? 0), 2) }}">
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
                                
                                <!-- Información de cuenta corriente en observaciones -->
                                @if($distributorTechnicalRecord->final_amount != $distributorTechnicalRecord->total_amount)
                                    <div class="alert alert-info mb-2">
                                        <small>
                                            <strong>Información de Cuenta Corriente:</strong><br>
                                            @if($distributorTechnicalRecord->total_amount > $distributorTechnicalRecord->final_amount)
                                                • Se aplicó un crédito de cuenta corriente de ${{ number_format(abs($distributorTechnicalRecord->total_amount - $distributorTechnicalRecord->final_amount), 2) }} a esta compra
                                            @else
                                                • Se agregó una deuda de cuenta corriente de ${{ number_format(abs($distributorTechnicalRecord->total_amount - $distributorTechnicalRecord->final_amount), 2) }} a esta compra
                                            @endif
                                            <br>• Total original: ${{ number_format($distributorTechnicalRecord->total_amount, 2) }}
                                            <br>• Monto final: ${{ number_format($distributorTechnicalRecord->final_amount, 2) }}
                                        </small>
                                    </div>
                                @endif
                                
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

                            <!-- Campo oculto para el ajuste de cuenta corriente -->
                            <input type="hidden" name="balance_adjustment" id="balance_adjustment_hidden" value="0">

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

            // Variable global para el índice de productos
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
                
                // Incrementar el índice para el próximo producto
                productIndex++;
            }

            $('#add-product').click(function() {
                addProductRow();
            });
            
            // Evento para eliminar productos
            $(document).on('click', '.remove-product', function() {
                const index = $(this).data('index');
                $(`.product-row[data-index="${index}"]`).remove();
                
                // Recalcular índices después de eliminar
                $('.product-row').each(function(newIndex) {
                    const oldIndex = $(this).data('index');
                    $(this).attr('data-index', newIndex);
                    
                    // Actualizar los nombres de los campos
                    $(this).find('select[name^="products_purchased["]').attr('name', `products_purchased[${newIndex}][product_id]`);
                    $(this).find('input[name^="products_purchased["]').each(function() {
                        const fieldName = $(this).attr('name').match(/\[([^\]]+)\]$/)[1];
                        $(this).attr('name', `products_purchased[${newIndex}][${fieldName}]`);
                    });
                    
                    // Actualizar el data-index del botón de eliminar
                    $(this).find('.remove-product').attr('data-index', newIndex);
                });
                
                // Actualizar el productIndex global
                productIndex = $('.product-row').length;
                
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
            
            // Verificar si se debe usar la cuenta corriente
            const useCurrentAccount = $('#use_current_account').is(':checked');
            
            // Obtener el saldo de cuenta corriente
            const currentBalanceText = $('#current_balance').val().replace(/[^\d,-]/g, '').replace(',', '.');
            const currentBalance = parseFloat(currentBalanceText) || 0;
            
            // Calcular ajuste de cuenta corriente solo si está marcado el checkbox
            let balanceAdjustment = 0;
            if (useCurrentAccount) {
                if (currentBalance > 0) {
                    // Si tiene deuda, se suma a la compra
                    balanceAdjustment = currentBalance;
                } else if (currentBalance < 0) {
                    // Si tiene crédito, se descuenta de la compra (valor negativo)
                    balanceAdjustment = currentBalance; // Mantener el valor negativo
                }
            }
            
            // Mostrar el ajuste en el campo correspondiente (valor absoluto para mostrar)
            $('#balance_adjustment').val(Math.abs(balanceAdjustment).toFixed(2).replace('.', ','));
            $('#balance_adjustment_hidden').val(balanceAdjustment.toString());
            
            // Calcular monto final: total + ajuste de cuenta corriente
            const finalAmount = Math.max(0, total + balanceAdjustment);
            
            $('#final_amount').val('$' + finalAmount.toFixed(2));
        }

        // Calcular monto final inicial
        calculateFinalAmount();
        
        // Event listener para el checkbox de usar cuenta corriente
        $('#use_current_account').on('change', function() {
            // Actualizar el campo oculto
            $('#use_current_account_hidden').val(this.checked ? '1' : '0');
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