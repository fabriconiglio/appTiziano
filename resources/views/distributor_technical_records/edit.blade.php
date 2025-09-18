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
                                                       value="{{ number_format($currentBalanceWithoutThisRecord, 2, ',', '.') }}" 
                                                       readonly style="background-color: #e9ecef;">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Estado</label>
                                            <div class="mt-2">
                                                @if($currentBalanceWithoutThisRecord > 0)
                                                    <span class="badge bg-danger fs-6">Con Deuda</span>
                                                @elseif($currentBalanceWithoutThisRecord < 0)
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
                                                @if($currentBalanceWithoutThisRecord > 0)
                                                    La compra se sumará a la deuda existente
                                                @elseif($currentBalanceWithoutThisRecord < 0)
                                                    El crédito se aplicará a esta compra
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
                                    
                                    <!-- Explicación del cálculo -->
                                    @if($currentBalanceWithoutThisRecord != 0)
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <h6 class="alert-heading">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Cálculo del Monto Final (Editando Ficha Técnica)
                                                </h6>
                                                @if($currentBalanceWithoutThisRecord > 0)
                                                    <p class="mb-1">
                                                        <strong>Deuda actual (sin esta ficha):</strong> ${{ number_format($currentBalanceWithoutThisRecord, 2, ',', '.') }}<br>
                                                        <strong>+ Compra actual:</strong> $<span id="purchase_amount_display">0,00</span><br>
                                                        <strong>= Total a pagar:</strong> $<span id="total_debt_display">0,00</span>
                                                    </p>
                                                @else
                                                    <p class="mb-1">
                                                        <strong>Crédito disponible (sin esta ficha):</strong> ${{ number_format(abs($currentBalanceWithoutThisRecord), 2, ',', '.') }}<br>
                                                        <strong>- Compra actual:</strong> $<span id="purchase_amount_display">0,00</span><br>
                                                        <strong>= Total a pagar:</strong> $<span id="total_debt_display">0,00</span>
                                                    </p>
                                                @endif
                                                <small class="text-muted">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Nota: El saldo mostrado excluye esta ficha técnica para evitar duplicación en el cálculo.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
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
                                <label class="form-label">
                                    Productos Comprados 
                                    <span class="badge bg-primary" id="product-counter">{{ count($distributorTechnicalRecord->products_purchased ?? []) }}</span>
                                </label>
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
                                                        @php
                                                            // Determinar si hay descuento aplicado
                                                            $hasDiscount = !empty($productData['discount_type']) && !empty($productData['discount_value']);
                                                            
                                                            // Obtener el producto para calcular el precio original
                                                            $supplierInventory = $supplierInventories->firstWhere('id', $productData['product_id']);
                                                            
                                                            // Calcular el precio original basado en el tipo de compra
                                                            $calculatedOriginalPrice = 0;
                                                            if ($supplierInventory) {
                                                                switch ($distributorTechnicalRecord->purchase_type) {
                                                                    case 'al_por_mayor':
                                                                        $calculatedOriginalPrice = $supplierInventory->precio_mayor ?: 0;
                                                                        break;
                                                                    case 'al_por_menor':
                                                                        $calculatedOriginalPrice = $supplierInventory->precio_menor ?: 0;
                                                                        break;
                                                                    default:
                                                                        $calculatedOriginalPrice = $supplierInventory->precio_menor ?: $supplierInventory->precio_mayor ?: 0;
                                                                        break;
                                                                }
                                                            }
                                                            
                                                            // Usar el precio original guardado si existe, sino calcularlo
                                                            $originalPrice = $productData['original_price'] ?? $calculatedOriginalPrice;
                                                            $discountedPrice = $productData['price'] ?? 0;
                                                            
                                                            // En edición, siempre mostrar el precio original sin descuento
                                                            $displayPrice = $originalPrice;
                                                        @endphp
                                                        <input type="text" class="form-control price-display" readonly 
                                                               value="${{ number_format($displayPrice, 2) }}">
                                                        <input type="hidden" class="price-value" name="products_purchased[{{ $index }}][price]" 
                                                               value="{{ $discountedPrice }}">
                                                        <input type="hidden" class="original-price-value" name="products_purchased[{{ $index }}][original_price]" 
                                                               value="{{ $originalPrice }}">
                                                        <input type="hidden" class="discount-type" name="products_purchased[{{ $index }}][discount_type]" 
                                                               value="{{ $productData['discount_type'] ?? '' }}">
                                                        <input type="hidden" class="discount-value" name="products_purchased[{{ $index }}][discount_value]" 
                                                               value="{{ $productData['discount_value'] ?? '' }}">
                                                        <input type="hidden" class="discount-reason" name="products_purchased[{{ $index }}][discount_reason]" 
                                                               value="{{ $productData['discount_reason'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Subtotal</label>
                                                        <div class="d-flex align-items-end">
                                                            <input type="text" class="form-control subtotal-display" readonly style="flex: 1; margin-right: 8px;"
                                                                   value="${{ number_format($productData['quantity'] * $discountedPrice, 2) }}">
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
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    Actualizar Ficha Técnica de Compra
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
                                <input type="hidden" class="original-price-value" name="products_purchased[${productIndex}][original_price]">
                                <input type="hidden" class="discount-type" name="products_purchased[${productIndex}][discount_type]">
                                <input type="hidden" class="discount-value" name="products_purchased[${productIndex}][discount_value]">
                                <input type="hidden" class="discount-reason" name="products_purchased[${productIndex}][discount_reason]">
                            </div>
                            <div class="col-md-2">
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
                
                // Actualizar el contador de productos
                updateProductCounter();
            }

            $('#add-product').click(function() {
                addProductRow();
            });
            
            // Evento para eliminar productos
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
                    
                    updateProductCounter();
                    calculateTotal();
                }, 10);
            });

            // Inicializar eventos para productos existentes
            $('.product-row').each(function() {
                const productRow = $(this);
                const productId = productRow.find('.product-description-select').val();
                
                // Verificar si ya tiene descuento aplicado
                const hasDiscount = productRow.find('.discount-type').val() && productRow.find('.discount-value').val();
                
                if (hasDiscount) {
                    // Si ya tiene descuento, usar los valores existentes
                    const originalPrice = parseFloat(productRow.find('.original-price-value').val()) || 0;
                    const discountedPrice = parseFloat(productRow.find('.price-value').val()) || 0;
                    const quantity = parseInt(productRow.find('.quantity-input').val()) || 0;
                    const subtotal = discountedPrice * quantity; // Usar precio con descuento para el subtotal
                    
                    productRow.find('.price-display').val('$' + originalPrice.toFixed(2));
                    productRow.find('.subtotal-display').val('$' + subtotal.toFixed(2));
                    
                    // Marcar botón como con descuento
                    productRow.find('.discount-product').removeClass('btn-outline-warning').addClass('btn-warning');
                    productRow.find('.discount-product').html('<i class="fas fa-check"></i>');
                } else if (productId) {
                    // Si no tiene descuento, obtener precio del producto
                    $.ajax({
                        url: '{{ route("api.supplier-inventories.get-product") }}',
                        method: 'GET',
                        data: { product_id: productId },
                        success: function(response) {
                            const price = response.precio_mayor || response.precio_menor || 0;
                            const priceDisplay = price > 0 ? '$' + parseFloat(price).toFixed(2) : 'N/A';
                            
                            productRow.find('.price-display').val(priceDisplay);
                            productRow.find('.price-value').val(price);
                            productRow.find('.original-price-value').val(price);
                            
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
            updateProductCounter();
            calculateFinalAmount();
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
            
            // Actualizar la explicación del cálculo
            updateCalculationExplanation(total, currentBalance, finalAmount);
        }
        
        // Función para actualizar la explicación del cálculo
        function updateCalculationExplanation(total, currentBalance, finalAmount) {
            const purchaseAmountDisplay = total.toFixed(2).replace('.', ',');
            const totalDebtDisplay = finalAmount.toFixed(2).replace('.', ',');
            
            $('#purchase_amount_display').text(purchaseAmountDisplay);
            $('#total_debt_display').text(totalDebtDisplay);
        }

        // Calcular monto final inicial
        calculateFinalAmount();
        
        // Event listener para el checkbox de usar cuenta corriente
        $('#use_current_account').on('change', function() {
            // Actualizar el campo oculto
            $('#use_current_account_hidden').val(this.checked ? '1' : '0');
            calculateFinalAmount();
        });

        // Validación para el botón de guardar
        $('#submit-btn').on('click', function() {
            if ($('#products-container').is(':visible') && $('#products-container').find('.product-row').length === 0) {
                showCustomAlert('Por favor, agrega al menos un producto a la ficha técnica.');
                return false;
            }
            return true;
        });

        // Variables para el modal de descuento
        let currentProductRow = null;
        let currentProductIndex = null;

        // Event listener para el botón de descuento
        $(document).on('click', '.discount-product', function() {
            currentProductRow = $(this).closest('.product-row');
            currentProductIndex = $(this).data('index');
            
            // Verificar que hay un producto seleccionado
            const productId = currentProductRow.find('.product-description-select').val();
            if (!productId) {
                showCustomAlert('Por favor, selecciona un producto primero.');
                return;
            }
            
            // Obtener datos del producto
            const productName = currentProductRow.find('.product-description-select option:selected').text();
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
                showCustomAlert('Por favor, completa todos los campos del descuento.');
                return;
            }
            
            // Guardar datos del descuento en los campos ocultos
            currentProductRow.find('.discount-type').val(discountType);
            currentProductRow.find('.discount-value').val(discountValue);
            currentProductRow.find('.discount-reason').val(discountReason);
            
            // Calcular nuevo precio unitario usando el precio original real
            const originalPrice = parseFloat(currentProductRow.find('.original-price-value').val()) || 0;
            const quantity = parseInt(currentProductRow.find('.quantity-input').val()) || 0;
            const originalSubtotal = originalPrice * quantity;
            
            let newSubtotal = originalSubtotal;
            if (discountType === 'percentage') {
                const discountAmount = (originalSubtotal * discountValue) / 100;
                newSubtotal = Math.max(0, originalSubtotal - discountAmount);
            } else if (discountType === 'fixed') {
                newSubtotal = Math.max(0, originalSubtotal - discountValue);
            }
            
            // Actualizar precio unitario (dividir por cantidad)
            const newUnitPrice = quantity > 0 ? newSubtotal / quantity : 0;
            
            // Actualizar campos (NO sobrescribir el precio original)
            currentProductRow.find('.price-value').val(newUnitPrice);
            currentProductRow.find('.price-display').val('$' + originalPrice.toFixed(2));
            
            // Recalcular subtotal
            calculateSubtotal(currentProductRow);
            
            // Cerrar modal
            $('#discountModal').modal('hide');
            
            // Mostrar indicador visual de descuento aplicado
            currentProductRow.find('.discount-product').removeClass('btn-outline-warning').addClass('btn-warning');
            currentProductRow.find('.discount-product').html('<i class="fas fa-check"></i>');
        });

        // Limpiar descuento al cambiar producto
        $(document).on('select2:select', '.product-description-select', function() {
            const productRow = $(this).closest('.product-row');
            productRow.find('.discount-type').val('');
            productRow.find('.discount-value').val('');
            productRow.find('.discount-reason').val('');
            productRow.find('.original-price-value').val('');
            productRow.find('.discount-product').removeClass('btn-warning').addClass('btn-outline-warning');
            productRow.find('.discount-product').html('<i class="fas fa-percentage"></i>');
        });

        // Inicializar indicadores visuales para productos existentes con descuentos
        $('.product-row').each(function() {
            const productRow = $(this);
            const hasDiscount = productRow.find('.discount-type').val() && productRow.find('.discount-value').val();
            
            if (hasDiscount) {
                productRow.find('.discount-product').removeClass('btn-outline-warning').addClass('btn-warning');
                productRow.find('.discount-product').html('<i class="fas fa-check"></i>');
            }
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
                        showCustomAlert('Error al eliminar la foto');
                    }
                });
            }
        }

        // Función para mostrar modal personalizado en lugar de alert
        function showCustomAlert(message) {
            $('#customAlertMessage').text(message);
            $('#customAlertModal').modal('show');
        }
    </script>
@endpush

<!-- Modal personalizado para alertas -->
<div class="modal fade" id="customAlertModal" tabindex="-1" aria-labelledby="customAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="customAlertModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Advertencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info me-3 fs-4"></i>
                    <p class="mb-0" id="customAlertMessage">Mensaje de alerta</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <i class="fas fa-check me-1"></i>
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>

@endsection 