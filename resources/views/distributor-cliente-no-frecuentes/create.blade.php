@extends('layouts.app')

@section('title', 'Nuevo Cliente No Frecuente - Distribuidora')

@push('styles')
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
        .product-row .col-md-4 {
            min-width: 200px;
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
        /* Ajustar el contenedor de acciones */
        .product-row .d-flex {
            gap: 4px;
        }
        /* Ajustar el botón de eliminar */
        .product-row .btn-sm {
            height: 45px;
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus"></i> Nuevo Cliente No Frecuente - Distribuidora
                    </h5>
                    <a href="{{ route('distributor-cliente-no-frecuentes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('distributor-cliente-no-frecuentes.store') }}" method="POST">
                        @csrf

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
                                       value="{{ old('nombre') }}"
                                       placeholder="Opcional - Dejar vacío si no se conoce">
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                       id="telefono" name="telefono" 
                                       value="{{ old('telefono') }}"
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
                                       value="{{ old('fecha', date('Y-m-d')) }}" 
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
                                           value="{{ old('monto') }}" required
                                           placeholder="0.00">
                                </div>
                                @error('monto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label for="purchase_type" class="form-label">Tipo de Compra</label>
                                <select class="form-select @error('purchase_type') is-invalid @enderror"
                                        id="purchase_type" name="purchase_type">
                                    <option value="">Seleccionar tipo</option>
                                    <option value="al_por_mayor" {{ old('purchase_type') == 'al_por_mayor' ? 'selected' : '' }}>Al por Mayor</option>
                                    <option value="al_por_menor" {{ old('purchase_type') == 'al_por_menor' ? 'selected' : '' }}>Al por Menor</option>
                                </select>
                                @error('purchase_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">
                                    Productos Comprados 
                                    <span class="badge bg-primary" id="product-counter">0</span>
                                </label>
                                <div id="products-container">
                                    <div class="text-center text-muted py-4" id="no-products-message">
                                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                        <p>No hay productos agregados. Haz clic en "Agregar Producto" para comenzar.</p>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-product">
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
                                          placeholder="Cualquier observación adicional sobre la venta o cliente...">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('distributor-cliente-no-frecuentes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cliente
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
$(document).ready(function() {
    // Establecer fecha actual por defecto
    const fechaInput = document.getElementById('fecha');
    if (!fechaInput.value) {
        fechaInput.value = new Date().toISOString().split('T')[0];
    }

    function addProductRow() {
        // Recalcular el índice basado en productos existentes
        const existingProducts = document.querySelectorAll('.product-row');
        const productIndex = existingProducts.length;

        const productRow = `
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
                    <div class="col-md-2">
                        <label class="form-label">Precio</label>
                        <input type="text" class="form-control price-display" readonly>
                        <input type="hidden" class="price-value" name="products_purchased[${productIndex}][price]">
                        <input type="hidden" class="original-price-value" name="products_purchased[${productIndex}][original_price]">
                        <input type="hidden" class="discount-type" name="products_purchased[${productIndex}][discount_type]">
                        <input type="hidden" class="discount-value" name="products_purchased[${productIndex}][discount_value]">
                        <input type="hidden" class="discount-reason" name="products_purchased[${productIndex}][discount_reason]">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Subtotal</label>
                        <input type="text" class="form-control subtotal-display" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Acciones</label>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-outline-warning btn-sm discount-product" 
                                    data-index="${productIndex}" style="height: 45px; min-width: 45px; flex-shrink: 0;" 
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
                            console.log('API Response:', data); // Debug
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
                                    
                                    const result = {
                                        id: item.id,
                                        text: displayText,
                                        precio_mayor: item.precio_mayor || 0,
                                        precio_menor: item.precio_menor || 0,
                                        stock: item.stock_quantity || 0,
                                        stock_quantity: item.stock_quantity || 0
                                    };
                                    console.log('Mapped result:', result); // Debug
                                    return result;
                                })
                            };
                        },
                cache: true
            }
        });

        // Evento cuando se selecciona un producto
        $(`.product-row[data-index="${productIndex}"] .product-description-select`).on('select2:select', function(e) {
            const data = e.params.data;
            const productRow = $(this).closest('.product-row');
            
            console.log('Producto seleccionado:', data); // Debug
            
            // Guardar los precios en el elemento del producto
            productRow.data('precio-mayor', data.precio_mayor || 0);
            productRow.data('precio-menor', data.precio_menor || 0);
            
            // Llenar los campos con la información del producto
            const stockValue = data.stock_quantity !== undefined ? data.stock_quantity : (data.stock !== undefined ? data.stock : 0);
            productRow.find('.stock-display').val(stockValue);
            
            // Establecer cantidad por defecto
            productRow.find('.quantity-input').val(1);
            
            // Actualizar precio según el tipo seleccionado
            updateProductPrice(productRow);
            
            // Calcular subtotal inicial
            updateSubtotal(productRow);
            updateTotal();
            updateProductCounter();
        });

        // Evento cuando cambia la cantidad
        $(`.product-row[data-index="${productIndex}"] .quantity-input`).on('input', function() {
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

    }

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
        
        productRow.find('.price-display').val('$' + selectedPrice.toFixed(2));
        productRow.find('.price-value').val(selectedPrice);
        productRow.find('.original-price-value').val(selectedPrice);
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
            const subtotalText = $(this).find('.subtotal-display').val();
            const subtotal = parseFloat(subtotalText.replace('$', '')) || 0;
            total += subtotal;
        });
        
        $('#monto').val(total.toFixed(2));
    }

    function updateProductCounter() {
        const count = $('.product-row').length;
        $('#product-counter').text(count);
        
        if (count > 0) {
            $('#no-products-message').hide();
        } else {
            $('#no-products-message').show();
        }
    }

    // Evento para agregar producto
    $('#add-product').on('click', function() {
        addProductRow();
    });

    // Evento para eliminar producto
    $(document).on('click', '.remove-product', function() {
        $(this).closest('.product-row').remove();
        updateProductCounter();
        updateTotal();
    });

    // Evento para aplicar descuento
    $(document).on('click', '.discount-product', function() {
        const productIndex = $(this).data('index');
        const productRow = $(`.product-row[data-index="${productIndex}"]`);
        
        // Llenar el modal con la información del producto
        const productName = productRow.find('.product-description-select option:selected').text();
        const originalPrice = parseFloat(productRow.find('.original-price-value').val()) || 0;
        const quantity = parseFloat(productRow.find('.quantity-input').val()) || 0;
        const originalSubtotal = originalPrice * quantity;
        
        $('#product_name_discount').val(productName);
        $('#original_price').text(originalPrice.toFixed(2));
        $('#original_subtotal').text(originalSubtotal.toFixed(2));
        $('#new_subtotal').text(originalSubtotal.toFixed(2));
        
        // Limpiar el formulario
        $('#discount_type').val('');
        $('#discount_value').val('');
        $('#discount_reason').val('');
        
        // Mostrar el modal
        $('#discountModal').modal('show');
        
        // Guardar el índice del producto para aplicar el descuento
        $('#discountModal').data('product-index', productIndex);
    });

    // Cambio en el tipo de descuento
    $('#discount_type').on('change', function() {
        const symbol = $(this).val() === 'percentage' ? '%' : '$';
        $('#discount_symbol').text(symbol);
        $('#discount_value').attr('placeholder', symbol === '%' ? '0' : '0.00');
    });

    // Cálculo del nuevo subtotal cuando cambia el valor del descuento
    $('#discount_value').on('input', function() {
        const discountType = $('#discount_type').val();
        const discountValue = parseFloat($(this).val()) || 0;
        const originalSubtotal = parseFloat($('#original_subtotal').text()) || 0;
        
        let newSubtotal = originalSubtotal;
        
        if (discountType === 'percentage') {
            newSubtotal = originalSubtotal * (1 - discountValue / 100);
        } else if (discountType === 'fixed') {
            newSubtotal = Math.max(0, originalSubtotal - discountValue);
        }
        
        $('#new_subtotal').text(newSubtotal.toFixed(2));
    });

    // Aplicar descuento
    $('#apply_discount').on('click', function() {
        const productIndex = $('#discountModal').data('product-index');
        const productRow = $(`.product-row[data-index="${productIndex}"]`);
        
        const discountType = $('#discount_type').val();
        const discountValue = parseFloat($('#discount_value').val()) || 0;
        const discountReason = $('#discount_reason').val();
        const originalPrice = parseFloat(productRow.find('.original-price-value').val()) || 0;
        const quantity = parseFloat(productRow.find('.quantity-input').val()) || 0;
        
        let newPrice = originalPrice;
        
        if (discountType === 'percentage') {
            newPrice = originalPrice * (1 - discountValue / 100);
        } else if (discountType === 'fixed') {
            newPrice = Math.max(0, originalPrice - (discountValue / quantity));
        }
        
        // Actualizar los campos
        productRow.find('.price-value').val(newPrice.toFixed(2));
        productRow.find('.price-display').val('$' + newPrice.toFixed(2));
        productRow.find('.discount-type').val(discountType);
        productRow.find('.discount-value').val(discountValue);
        productRow.find('.discount-reason').val(discountReason);
        
        // Recalcular subtotal y total
        updateSubtotal(productRow);
        updateTotal();
        
        // Cerrar el modal
        $('#discountModal').modal('hide');
    });

    // Evento global para selección de productos
    $(document).on('select2:select', '.product-description-select', function(e) {
        const data = e.params.data;
        const productRow = $(this).closest('.product-row');
        
        console.log('Producto seleccionado:', data); // Debug
        
        // Guardar los precios en el elemento del producto
        productRow.data('precio-mayor', data.precio_mayor || 0);
        productRow.data('precio-menor', data.precio_menor || 0);
        
        // Llenar los campos con la información del producto
        const stockValue = data.stock_quantity !== undefined ? data.stock_quantity : (data.stock !== undefined ? data.stock : 0);
        productRow.find('.stock-display').val(stockValue);
        
        // Establecer cantidad por defecto
        productRow.find('.quantity-input').val(1);
        
        // Validar stock después de seleccionar producto
        const quantity = parseInt(productRow.find('.quantity-input').val()) || 0;
        const stock = parseInt(data.stock_quantity !== undefined ? data.stock_quantity : (data.stock !== undefined ? data.stock : 0)) || 0;
        
        if (quantity > stock) {
            productRow.find('.quantity-input').addClass('is-invalid');
            productRow.find('.stock-display').addClass('is-invalid');
        } else {
            productRow.find('.quantity-input').removeClass('is-invalid');
            productRow.find('.stock-display').removeClass('is-invalid');
        }
        
        // Actualizar precio según el tipo seleccionado
        updateProductPrice(productRow);
        
        // Calcular subtotal inicial
        updateSubtotal(productRow);
        updateTotal();
        updateProductCounter();
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

    // Validación en tiempo real del monto
    $('#monto').on('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });
});
</script>
@endpush
@endsection
