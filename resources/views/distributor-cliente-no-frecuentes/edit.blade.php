@extends('layouts.app')

@section('title', 'Editar Cliente No Frecuente - Distribuidora')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .product-row {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }
        
        .remove-product {
            transition: all 0.2s ease;
        }
        
        .remove-product:hover {
            transform: scale(1.05);
        }
        
        .discount-product {
            transition: all 0.2s ease;
        }
        
        .discount-product:hover {
            transform: scale(1.05);
        }
        
        /* Estilos para inputs más grandes - solo dentro de product-row */
        .product-row .form-control,
        .product-row .form-select {
            height: 45px;
            font-size: 12px;
            padding: 8px 12px;
        }
        /* Eliminar CSS que puede estar causando problemas en el modal */
        /* Forzar padding específico para el input del modal */
        #product_name_discount {
            padding-left: 0 !important;
            padding-right: 0 !important;
            text-indent: 0 !important;
            margin-left: 0 !important;
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
        .product-row .col-md-4 {
            min-width: 200px;
        }
        /* Hacer más anchos los inputs de precio y subtotal */
        .product-row .price-column,
        .product-row .subtotal-column {
            min-width: 120px;
        }
        /* Hacer el input del subtotal más ancho */
        .product-row .subtotal-display {
            min-width: 100px;
            width: 100%;
        }
        /* Ajustar el contenedor de acciones */
        .product-row .d-flex {
            gap: 4px;
        }
        /* Ajustar el botón de eliminar */
        .product-row .btn-sm {
            height: 45px;
            min-width: 45px;
            font-size: 12px;
        }
        
    </style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit"></i> Editar Cliente No Frecuente - Distribuidora
                    </h5>
                    <a href="{{ route('distributor-cliente-no-frecuentes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('distributor-cliente-no-frecuentes.update', $distributorClienteNoFrecuente) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Información del Cliente -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user"></i> Información del Cliente
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del Cliente</label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" 
                                       value="{{ old('nombre', $distributorClienteNoFrecuente->nombre) }}"
                                       placeholder="Opcional - Dejar vacío si no se conoce">
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                       id="telefono" name="telefono" 
                                       value="{{ old('telefono', $distributorClienteNoFrecuente->telefono) }}"
                                       placeholder="Opcional - Número de contacto">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información de la Venta -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-shopping-cart"></i> Información de la Venta
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="fecha" class="form-label">Fecha de la Venta *</label>
                                <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                                       id="fecha" name="fecha" 
                                       value="{{ old('fecha', $distributorClienteNoFrecuente->fecha->format('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}" required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="monto" class="form-label">Valor de la Venta *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control @error('monto') is-invalid @enderror" 
                                           id="monto" name="monto" 
                                           value="{{ old('monto', $distributorClienteNoFrecuente->monto) }}" required
                                           placeholder="0.00">
                                </div>
                                @error('monto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="purchase_type" class="form-label">Tipo de Compra</label>
                                <select class="form-select @error('purchase_type') is-invalid @enderror"
                                        id="purchase_type" name="purchase_type">
                                    <option value="">Seleccionar tipo</option>
                                    <option value="al_por_mayor" {{ old('purchase_type', $distributorClienteNoFrecuente->purchase_type) == 'al_por_mayor' ? 'selected' : '' }}>Al por Mayor</option>
                                    <option value="al_por_menor" {{ old('purchase_type', $distributorClienteNoFrecuente->purchase_type) == 'al_por_menor' ? 'selected' : '' }}>Al por Menor</option>
                                </select>
                                @error('purchase_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Productos Comprados -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-shopping-cart"></i> Productos Comprados
                                </h6>
                                <p class="text-muted mb-3">Agregue los productos que compró el cliente no frecuente</p>
                                
                                <div id="products-container">
                                    @if(old('products_purchased') || (isset($distributorClienteNoFrecuente->products_purchased) && count($distributorClienteNoFrecuente->products_purchased) > 0))
                                        @php
                                            $productsData = old('products_purchased', $distributorClienteNoFrecuente->products_purchased ?? []);
                                        @endphp
                                        @foreach($productsData as $index => $product)
                                            @php
                                                $supplierInventory = \App\Models\SupplierInventory::find($product['product_id']);
                                            @endphp
                                            <div class="product-row" data-index="{{ $index }}">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Buscar Producto</label>
                                                        <select class="form-select product-description-select" name="products_purchased[{{ $index }}][product_id]" required>
                                                            <option value="">Buscar por nombre, descripción o marca...</option>
                                                            @if($supplierInventory)
                                                                <option value="{{ $product['product_id'] }}" selected>
                                                                    {{ $supplierInventory->product_name }} - {{ $supplierInventory->description ?? '' }} - {{ $supplierInventory->distributorBrand->name ?? 'Sin marca' }}
                                                                </option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label">Cantidad</label>
                                                        <input type="number" class="form-control quantity-input" 
                                                               name="products_purchased[{{ $index }}][quantity]" 
                                                               value="{{ $product['quantity'] ?? 1 }}"
                                                               min="1" required>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label">Stock</label>
                                                        <input type="text" class="form-control stock-display" readonly value="{{ $supplierInventories->firstWhere('id', $product['product_id'])->stock_quantity ?? 0 }}">
                                                    </div>
                                                    <div class="col-md-2 price-column">
                                                        <label class="form-label">Precio</label>
                                                        @php
                                                            // Determinar si hay descuento aplicado
                                                            $hasDiscount = !empty($product['discount_type']) && !empty($product['discount_value']);
                                                            
                                                            // Obtener el producto para calcular el precio original
                                                            $supplierInventory = \App\Models\SupplierInventory::find($product['product_id']);
                                                            
                                                            // Calcular precio original basado en el tipo de compra
                                                            $purchaseType = old('purchase_type', $distributorClienteNoFrecuente->purchase_type);
                                                            $calculatedOriginalPrice = 0;
                                                            if ($supplierInventory) {
                                                                if ($purchaseType === 'al_por_mayor') {
                                                                    $calculatedOriginalPrice = $supplierInventory->precio_mayor ?? 0;
                                                                } else {
                                                                    $calculatedOriginalPrice = $supplierInventory->precio_menor ?? 0;
                                                                }
                                                            }
                                                            
                                                            // Usar el precio original guardado si existe, sino calcularlo
                                                            $originalPrice = $product['original_price'] ?? $calculatedOriginalPrice;
                                                            $discountedPrice = $product['price'] ?? $originalPrice;
                                                            
                                                            // Asegurar que siempre tengamos un precio válido para el subtotal
                                                            $priceForSubtotal = $discountedPrice > 0 ? $discountedPrice : $originalPrice;
                                                            
                                                            // En edición, siempre mostrar el precio original sin descuento
                                                            $displayPrice = $originalPrice;
                                                        @endphp
                                                        <input type="text" class="form-control price-display" readonly 
                                                               value="${{ number_format($displayPrice, 2) }}">
                                                        <input type="hidden" class="price-value" name="products_purchased[{{ $index }}][price]" 
                                                               value="{{ $priceForSubtotal }}">
                                                        <input type="hidden" class="original-price-value" name="products_purchased[{{ $index }}][original_price]" 
                                                               value="{{ $originalPrice }}">
                                                        <input type="hidden" class="discount-type" name="products_purchased[{{ $index }}][discount_type]" 
                                                               value="{{ $product['discount_type'] ?? '' }}">
                                                        <input type="hidden" class="discount-value" name="products_purchased[{{ $index }}][discount_value]" 
                                                               value="{{ $product['discount_value'] ?? '' }}">
                                                        <input type="hidden" class="discount-reason" name="products_purchased[{{ $index }}][discount_reason]" 
                                                               value="{{ $product['discount_reason'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-2 subtotal-column">
                                                        <label class="form-label">Subtotal</label>
                                                        <div class="d-flex align-items-end">
                                                            <input type="text" class="form-control subtotal-display" readonly style="flex: 1; margin-right: 8px;"
                                                                   value="${{ number_format(($product['quantity'] ?? 1) * $priceForSubtotal, 2) }}">
                                                            <button type="button" class="btn {{ $hasDiscount ? 'btn-warning' : 'btn-outline-warning' }} btn-sm discount-product" 
                                                                    data-index="{{ $index }}" style="height: 45px; min-width: 45px; flex-shrink: 0; margin-right: 4px;" 
                                                                    title="Aplicar descuento">
                                                                <i class="fas {{ $hasDiscount ? 'fa-check' : 'fa-percentage' }}"></i>
                                                            </button>
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
                                
                                <button type="button" class="btn btn-outline-primary" id="add-product">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-sticky-note"></i> Observaciones
                                </h6>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Cualquier observación adicional sobre la venta o cliente...">{{ old('observaciones', $distributorClienteNoFrecuente->observaciones) }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información del registro -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Información del Registro
                                    </h6>
                                    <p class="mb-1">
                                        <strong>Registrado por:</strong> {{ $distributorClienteNoFrecuente->user->name }}
                                    </p>
                                    <p class="mb-0">
                                        <strong>Fecha de registro:</strong> {{ $distributorClienteNoFrecuente->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('distributor-cliente-no-frecuentes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para aplicar descuento -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalLabel">Aplicar Descuento al Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="discountForm">
                    <div class="mb-3">
                        <label for="product_name_discount" class="form-label">Producto</label>
                        <input type="text" class="form-control" id="product_name_discount" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="discount_type" class="form-label">Tipo de Descuento</label>
                        <select class="form-select" id="discount_type" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="percentage">Porcentaje (%)</option>
                            <option value="fixed">Monto fijo ($)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="discount_value" class="form-label">Valor del Descuento</label>
                        <div class="input-group">
                            <span class="input-group-text" id="discount_symbol">$</span>
                            <input type="number" step="0.01" class="form-control" id="discount_value" 
                                   placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="discount_reason" class="form-label">Motivo del Descuento</label>
                        <textarea class="form-control" id="discount_reason" rows="2" 
                                  placeholder="Ej: Cliente frecuente, promoción especial, etc."></textarea>
                    </div>
                    <div class="alert alert-info">
                        <strong>Precio original:</strong> $<span id="original_price">0.00</span><br>
                        <strong>Subtotal original:</strong> $<span id="original_subtotal">0.00</span><br>
                        <strong>Nuevo subtotal:</strong> $<span id="new_subtotal">0.00</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="apply_discount">
                    <i class="fas fa-percentage"></i> Aplicar Descuento
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let productIndex = 0;
    let currentDiscountProductIndex = null;
    
    // Inicializar productos existentes
    initializeExistingProducts();
    
    function initializeExistingProducts() {
        $('.product-row').each(function() {
            const productRow = $(this);
            
            // Inicializar Select2
            initializeProductSelect(productRow);
            
            // Cargar datos que ya están en el formulario
            const originalPrice = productRow.find('.original-price-value').val();
            const price = productRow.find('.price-value').val();
            const stock = productRow.find('.stock-display').val();
            
            console.log('Datos del formulario:', { originalPrice, price, stock });
            
            if (originalPrice) {
                productRow.data('precio-mayor', originalPrice);
                productRow.data('precio-menor', originalPrice);
            }
            if (stock) {
                productRow.data('stock', stock);
            }
            
            // Los precios y subtotales ya están calculados en el HTML
            // Solo necesitamos actualizar el total general
        });
        
        updateTotal();
    }
    
    function loadProductData(productRow, productId) {
        console.log('Cargando producto con ID:', productId);
        
        // Buscar el producto por ID usando la API de búsqueda
        $.ajax({
            url: '/api/supplier-inventories/search',
            method: 'GET',
            data: { q: '', id: productId },
            success: function(response) {
                console.log('Respuesta de la API:', response);
                if (response && response.length > 0) {
                    const product = response[0];
                    console.log('Producto encontrado:', product);
                    
                    productRow.data('precio-mayor', product.precio_mayor || 0);
                    productRow.data('precio-menor', product.precio_menor || 0);
                    const stockValue = product.stock_quantity !== undefined ? product.stock_quantity : (product.stock !== undefined ? product.stock : 0);
                    productRow.data('stock', stockValue);
                    
                    // Configurar Select2 con el producto seleccionado
                    const select = productRow.find('.product-description-select');
                    select.empty().append(new Option(`${product.product_name} - ${product.description || ''} - ${product.brand || 'Sin marca'}`, product.id, true, true));
                    
                    // Actualizar campos
                    productRow.find('.stock-display').val(stockValue);
                    updateProductPrice(productRow);
                    updateSubtotal(productRow);
                } else {
                    console.log('Producto no encontrado en API, usando datos del formulario');
                    // Si no se encuentra el producto, intentar cargar desde los datos del formulario
                    const originalPrice = productRow.find('.original-price-value').val();
                    const price = productRow.find('.price-value').val();
                    const stock = productRow.find('.stock-display').val();
                    
                    console.log('Datos del formulario:', { originalPrice, price, stock });
                    
                    if (originalPrice) {
                        productRow.data('precio-mayor', originalPrice);
                        productRow.data('precio-menor', originalPrice);
                    }
                    if (stock) {
                        productRow.data('stock', stock);
                    }
                    
                    updateProductPrice(productRow);
                    updateSubtotal(productRow);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error en la API:', error);
                // En caso de error, usar los datos que ya están en el formulario
                const originalPrice = productRow.find('.original-price-value').val();
                const price = productRow.find('.price-value').val();
                const stock = productRow.find('.stock-display').val();
                
                console.log('Usando datos del formulario por error:', { originalPrice, price, stock });
                
                if (originalPrice) {
                    productRow.data('precio-mayor', originalPrice);
                    productRow.data('precio-menor', originalPrice);
                }
                if (stock) {
                    productRow.data('stock', stock);
                }
                
                updateProductPrice(productRow);
                updateSubtotal(productRow);
            }
        });
    }
    
    // Agregar nuevo producto
    $('#add-product').on('click', function() {
        addProductRow();
    });
    
    function addProductRow() {
        const productRowTemplate = `
            <div class="product-row" data-index="${productIndex}">
                <div class="row">
                    <div class="col-md-4">
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
                    <div class="col-md-2 price-column">
                        <label class="form-label">Precio</label>
                        <input type="text" class="form-control price-display" readonly>
                        <input type="hidden" class="price-value" name="products_purchased[${productIndex}][price]">
                        <input type="hidden" class="original-price-value" name="products_purchased[${productIndex}][original_price]">
                        <input type="hidden" class="discount-type" name="products_purchased[${productIndex}][discount_type]">
                        <input type="hidden" class="discount-value" name="products_purchased[${productIndex}][discount_value]">
                        <input type="hidden" class="discount-reason" name="products_purchased[${productIndex}][discount_reason]">
                    </div>
                    <div class="col-md-2 subtotal-column">
                        <label class="form-label">Subtotal</label>
                        <div class="d-flex align-items-end">
                            <input type="text" class="form-control subtotal-display" readonly style="flex: 1; margin-right: 8px;">
                            <button type="button" class="btn btn-outline-warning btn-sm discount-product" 
                                    data-index="${productIndex}" style="height: 45px; min-width: 45px; flex-shrink: 0; margin-right: 4px;" 
                                    title="Aplicar descuento">
                                <i class="fas fa-percentage"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-product" 
                                    data-index="${productIndex}" style="height: 45px; min-width: 45px; flex-shrink: 0;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#products-container').append(productRowTemplate);
        
        // Inicializar Select2 para el nuevo producto
        const newProductRow = $(`.product-row[data-index="${productIndex}"]`);
        initializeProductSelect(newProductRow);
        
        productIndex++;
    }
    
    function initializeProductSelect(productRow) {
        const select = productRow.find('.product-description-select');
        
        select.select2({
            placeholder: 'Buscar por nombre, descripción o marca...',
            allowClear: true,
            width: '100%',
            language: 'es',
            ajax: {
                url: '/api/supplier-inventories/search',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(item) {
                            return {
                                id: item.id,
                                text: `${item.product_name} - ${item.description || ''} - ${item.brand || 'Sin marca'}`,
                                price: item.precio_mayor || item.precio_menor || 0,
                                precio_mayor: item.precio_mayor || 0,
                                precio_menor: item.precio_menor || 0,
                                stock: item.stock_quantity || 0,
                                stock_quantity: item.stock_quantity || 0
                            };
                        })
                    };
                },
                cache: true
            }
        });
        
        // Evento cuando se selecciona un producto
        select.on('select2:select', function (e) {
            const data = e.params.data;
            const productRow = $(this).closest('.product-row');
            
            // Guardar datos del producto
            productRow.data('precio-mayor', data.precio_mayor || 0);
            productRow.data('precio-menor', data.precio_menor || 0);
            const stockValue = data.stock_quantity !== undefined ? data.stock_quantity : (data.stock !== undefined ? data.stock : 0);
            productRow.data('stock', stockValue);
            
            // Actualizar campos
            productRow.find('.stock-display').val(stockValue);
            
            // Validar stock después de seleccionar producto
            const quantity = parseInt(productRow.find('.quantity-input').val()) || 0;
            const stock = parseInt(stockValue) || 0;
            
            if (quantity > stock) {
                productRow.find('.quantity-input').addClass('is-invalid');
                productRow.find('.stock-display').addClass('is-invalid');
            } else {
                productRow.find('.quantity-input').removeClass('is-invalid');
                productRow.find('.stock-display').removeClass('is-invalid');
            }
            
            updateProductPrice(productRow);
            updateSubtotal(productRow);
            updateTotal();
        });
    }
    
    // Inicializar Select2 para productos existentes
    $('.product-description-select').each(function() {
        const productRow = $(this).closest('.product-row');
        initializeProductSelect(productRow);
    });
    
    // Remover producto
    $(document).on('click', '.remove-product', function() {
        $(this).closest('.product-row').remove();
        updateTotal();
    });
    
    // Event listener para el botón de descuento
    $(document).on('click', '.discount-product', function() {
        currentProductRow = $(this).closest('.product-row');
        currentDiscountProductIndex = $(this).data('index');
        
        // Verificar que hay un producto seleccionado
        const productId = currentProductRow.find('.product-description-select').val();
        if (!productId) {
            alert('Por favor, selecciona un producto primero.');
            return;
        }
        
        // Obtener datos del producto
        const productName = currentProductRow.find('.product-description-select option:selected').text().trim();
        const originalPrice = parseFloat(currentProductRow.find('.original-price-value').val()) || 0;
        const quantity = parseInt(currentProductRow.find('.quantity-input').val()) || 0;
        const originalSubtotal = originalPrice * quantity;
        
        // Llenar el modal
        $('#product_name_discount').val(productName);
        $('#original_price').text(originalPrice.toFixed(2));
        $('#original_subtotal').text(originalSubtotal.toFixed(2));
        $('#new_subtotal').text(originalSubtotal.toFixed(2));
        
        // Cargar datos existentes si hay descuento aplicado
        const existingDiscountType = currentProductRow.find('.discount-type').val();
        const existingDiscountValue = currentProductRow.find('.discount-value').val();
        const existingDiscountReason = currentProductRow.find('.discount-reason').val();
        
        if (existingDiscountType && existingDiscountValue) {
            $('#discount_type').val(existingDiscountType);
            $('#discount_value').val(existingDiscountValue);
            $('#discount_reason').val(existingDiscountReason);
            
            // Actualizar símbolo
            const symbol = existingDiscountType === 'percentage' ? '%' : '$';
            $('#discount_symbol').text(symbol);
            
            // Calcular preview
            updateDiscountPreview();
        } else {
            // Limpiar formulario
            $('#discount_type').val('');
            $('#discount_value').val('');
            $('#discount_reason').val('');
        }
        
        // Mostrar modal
        $('#discountModal').modal('show');
    });

    // Cambiar símbolo según tipo de descuento
    $('#discount_type').on('change', function() {
        const symbol = $(this).val() === 'percentage' ? '%' : '$';
        $('#discount_symbol').text(symbol);
        
        // Limpiar valor cuando cambie el tipo
        $('#discount_value').val('');
        updateDiscountPreview();
    });

    // Actualizar preview del descuento
    $('#discount_value').on('input', function() {
        updateDiscountPreview();
    });

    // Función para actualizar el preview del descuento
    function updateDiscountPreview() {
        const discountType = $('#discount_type').val();
        const discountValue = parseFloat($('#discount_value').val()) || 0;
        const originalPrice = parseFloat($('#original_price').text()) || 0;
        const quantity = parseInt(currentProductRow.find('.quantity-input').val()) || 0;
        const originalSubtotal = originalPrice * quantity;
        
        let newSubtotal = originalSubtotal;
        
        if (discountType && discountValue > 0) {
            if (discountType === 'percentage') {
                if (discountValue > 100) {
                    $('#discount_value').addClass('is-invalid');
                    return;
                } else {
                    $('#discount_value').removeClass('is-invalid');
                    const discountAmount = (originalSubtotal * discountValue) / 100;
                    newSubtotal = Math.max(0, originalSubtotal - discountAmount);
                }
            } else if (discountType === 'fixed') {
                if (discountValue > originalSubtotal) {
                    $('#discount_value').addClass('is-invalid');
                    return;
                } else {
                    $('#discount_value').removeClass('is-invalid');
                    newSubtotal = Math.max(0, originalSubtotal - discountValue);
                }
            }
        }
        
        $('#new_subtotal').text(newSubtotal.toFixed(2));
    }

    // Aplicar descuento
    $('#apply_discount').on('click', function() {
        const discountType = $('#discount_type').val();
        const discountValue = parseFloat($('#discount_value').val()) || 0;
        const discountReason = $('#discount_reason').val();
        
        if (!discountType || discountValue <= 0) {
            alert('Por favor, completa todos los campos del descuento.');
            return;
        }
        
        // Guardar datos del descuento en los campos ocultos
        currentProductRow.find('.discount-type').val(discountType);
        currentProductRow.find('.discount-value').val(discountValue);
        currentProductRow.find('.discount-reason').val(discountReason);
        
        // Calcular nuevo precio
        const originalPrice = parseFloat(currentProductRow.find('.original-price-value').val()) || 0;
        const quantity = parseInt(currentProductRow.find('.quantity-input').val()) || 0;
        const originalSubtotal = originalPrice * quantity;
        
        let newPrice = originalPrice;
        if (discountType === 'percentage') {
            const discountAmount = (originalPrice * discountValue) / 100;
            newPrice = Math.max(0, originalPrice - discountAmount);
        } else if (discountType === 'fixed') {
            const discountAmount = discountValue / quantity; // Dividir por cantidad para obtener descuento por unidad
            newPrice = Math.max(0, originalPrice - discountAmount);
        }
        
        // Actualizar campos
        currentProductRow.find('.price-value').val(newPrice);
        currentProductRow.find('.price-display').val('$' + originalPrice.toFixed(2)); // Mostrar precio original
        updateSubtotal(currentProductRow);
        updateTotal();
        
        // Actualizar botón de descuento
        currentProductRow.find('.discount-product').removeClass('btn-outline-warning').addClass('btn-warning');
        currentProductRow.find('.discount-product').html('<i class="fas fa-check"></i>');
        
        $('#discountModal').modal('hide');
    });
    
    function updateProductPrice(productRow) {
        const purchaseType = $('#purchase_type').val();
        const precioMayor = parseFloat(productRow.data('precio-mayor')) || 0;
        const precioMenor = parseFloat(productRow.data('precio-menor')) || 0;
        
        let selectedPrice = 0;
        if (purchaseType === 'al_por_mayor') {
            selectedPrice = precioMayor;
        } else if (purchaseType === 'al_por_menor') {
            selectedPrice = precioMenor;
        }
        
        // Guardar precio original
        productRow.find('.original-price-value').val(selectedPrice);
        
        // Aplicar descuento si existe
        const discountType = productRow.find('.discount-type').val();
        const discountValue = parseFloat(productRow.find('.discount-value').val()) || 0;
        
        let finalPrice = selectedPrice;
        if (discountType === 'percentage' && discountValue > 0) {
            finalPrice = selectedPrice * (1 - discountValue / 100);
        } else if (discountType === 'fixed' && discountValue > 0) {
            finalPrice = Math.max(0, selectedPrice - discountValue);
        }
        
        productRow.find('.price-display').val('$' + finalPrice.toFixed(2));
        productRow.find('.price-value').val(finalPrice);
    }
    
    function updateSubtotal(productRow) {
        const quantity = parseFloat(productRow.find('.quantity-input').val()) || 0;
        const price = parseFloat(productRow.find('.price-value').val()) || 0;
        const subtotal = quantity * price;
        
        productRow.find('.subtotal-display').val('$' + subtotal.toFixed(2));
    }
    
    function updateTotal() {
        let total = 0;
        $('.product-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.price-value').val()) || 0;
            total += quantity * price;
        });
        
        $('#monto').val(total.toFixed(2));
    }
    
    // Eventos para recálculo automático
    $(document).on('input', '.quantity-input', function() {
        const productRow = $(this).closest('.product-row');
        
        // Validar stock
        const quantity = parseInt($(this).val()) || 0;
        const stock = parseInt(productRow.find('.stock-display').val()) || 0;
        
        if (quantity > stock) {
            $(this).addClass('is-invalid');
            productRow.find('.stock-display').addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
            productRow.find('.stock-display').removeClass('is-invalid');
        }
        
        updateSubtotal(productRow);
        updateTotal();
    });
    
    // Evento global para cambio de tipo de compra
    $('#purchase_type').on('change', function() {
        $('.product-row').each(function() {
            const productRow = $(this);
            updateProductPrice(productRow);
            updateSubtotal(productRow);
        });
        updateTotal();
    });
    
    // Validación en tiempo real
    const montoInput = document.getElementById('monto');
    montoInput.addEventListener('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });
});
</script>
@endpush
@endsection