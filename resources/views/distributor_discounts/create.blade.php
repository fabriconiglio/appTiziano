@extends('layouts.app')

@section('title', 'Crear Descuento - Distribuidores')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-plus"></i> Crear Descuento</h1>
        <a href="{{ route('distributor-discounts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Errores en el formulario:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('distributor-discounts.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <!-- Información básica -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-info-circle"></i> Información Básica</h5>
                        
                        <div class="mb-3">
                            <label for="distributor_client_ids" class="form-label">Distribuidor <span class="text-danger">*</span></label>
                            <select name="distributor_client_ids[]" id="distributor_client_ids" class="form-select @error('distributor_client_ids') is-invalid @enderror" multiple required>
                                @foreach($distributorClients as $client)
                                    <option value="{{ $client->id }}" {{ collect(old('distributor_client_ids', []))->contains($client->id) ? 'selected' : '' }}>
                                        {{ $client->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('distributor_client_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <input type="text" name="description" id="description" 
                                   class="form-control @error('description') is-invalid @enderror" 
                                   value="{{ old('description') }}" required
                                   placeholder="Ej: Descuento por volumen, Promoción especial...">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="conditions" class="form-label">Condiciones especiales</label>
                            <textarea name="conditions" id="conditions" rows="3" 
                                      class="form-control @error('conditions') is-invalid @enderror"
                                      placeholder="Condiciones adicionales para aplicar el descuento...">{{ old('conditions') }}</textarea>
                            @error('conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Configuración del descuento -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-percent"></i> Configuración del Descuento</h5>

                        <div class="mb-3">
                            <label for="discount_type" class="form-label">Tipo de Descuento <span class="text-danger">*</span></label>
                            <select name="discount_type" id="discount_type" class="form-select @error('discount_type') is-invalid @enderror" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>
                                    Porcentaje (%)
                                </option>
                                <option value="fixed_amount" {{ old('discount_type') == 'fixed_amount' ? 'selected' : '' }}>
                                    Monto Fijo ($)
                                </option>
                                <option value="gift" {{ old('discount_type') == 'gift' ? 'selected' : '' }}>
                                    Regalo/Obsequio
                                </option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="discount_value_group">
                            <label for="discount_value" class="form-label">Valor del Descuento <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" id="discount_value" 
                                   class="form-control @error('discount_value') is-invalid @enderror" 
                                   step="0.01" min="0" value="{{ old('discount_value') }}"
                                   placeholder="0.00">
                            <div class="form-text" id="discount_value_help"></div>
                            @error('discount_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Información de regalo (solo visible cuando es tipo "gift") -->
                        <div class="mb-3" id="gift_products_group" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-gift"></i>
                                <strong>Descuento de Regalo:</strong> Se aplicará automáticamente un descuento del 100% al producto seleccionado.
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <!-- Aplicabilidad del producto -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-box"></i> Aplicabilidad del Producto</h5>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="applies_to_all_products" 
                                       id="applies_to_all_products" value="1" 
                                       {{ old('applies_to_all_products') ? 'checked' : '' }}>
                                <label class="form-check-label" for="applies_to_all_products">
                                    Aplicar a todos los productos del distribuidor
                                </label>
                            </div>
                        </div>

                        <div id="specific_product_group">
                            <div class="mb-3">
                                <label for="supplier_inventory_ids" class="form-label">Producto del Inventario</label>
                                <select name="supplier_inventory_ids[]" id="supplier_inventory_ids" class="form-select @error('supplier_inventory_ids') is-invalid @enderror" multiple>
                                    @foreach($supplierInventories as $inventory)
                                        <option value="{{ $inventory->id }}" {{ collect(old('supplier_inventory_ids', []))->contains($inventory->id) ? 'selected' : '' }}>
                                            {{ $inventory->product_name }}{{ $inventory->description ? ' - ' . $inventory->description : '' }}{{ $inventory->brand ? ' - ' . $inventory->brand : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_inventory_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                        </div>
                    </div>

                    <!-- Condiciones de aplicación -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-cogs"></i> Condiciones de Aplicación</h5>

                        <div class="mb-3">
                            <label for="minimum_quantity" class="form-label">Cantidad Mínima <span class="text-danger">*</span></label>
                            <input type="number" name="minimum_quantity" id="minimum_quantity" 
                                   class="form-control @error('minimum_quantity') is-invalid @enderror" 
                                   step="0.01" min="0" value="{{ old('minimum_quantity', 1) }}" required>
                            <div class="form-text">Cantidad mínima del producto para aplicar el descuento</div>
                            @error('minimum_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="minimum_amount" class="form-label">Monto Mínimo de Compra</label>
                            <input type="number" name="minimum_amount" id="minimum_amount" 
                                   class="form-control @error('minimum_amount') is-invalid @enderror" 
                                   step="0.01" min="0" value="{{ old('minimum_amount') }}"
                                   placeholder="0.00">
                            <div class="form-text">Monto mínimo total de la compra (opcional)</div>
                            @error('minimum_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="max_uses" class="form-label">Máximo Número de Usos</label>
                            <input type="number" name="max_uses" id="max_uses" 
                                   class="form-control @error('max_uses') is-invalid @enderror" 
                                   min="1" value="{{ old('max_uses') }}"
                                   placeholder="Sin límite">
                            <div class="form-text">Límite de veces que se puede usar este descuento (opcional)</div>
                            @error('max_uses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <!-- Vigencia -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-calendar"></i> Vigencia</h5>

                        <div class="mb-3">
                            <label for="valid_from" class="form-label">Válido Desde</label>
                            <input type="date" name="valid_from" id="valid_from" 
                                   class="form-control @error('valid_from') is-invalid @enderror" 
                                   value="{{ old('valid_from') }}">
                            <div class="form-text">Fecha de inicio de validez (opcional)</div>
                            @error('valid_from')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="valid_until" class="form-label">Válido Hasta</label>
                            <input type="date" name="valid_until" id="valid_until" 
                                   class="form-control @error('valid_until') is-invalid @enderror" 
                                   value="{{ old('valid_until') }}">
                            <div class="form-text">Fecha de fin de validez (opcional)</div>
                            @error('valid_until')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="col-md-6">
                        <h5 class="mb-3"><i class="fas fa-toggle-on"></i> Estado</h5>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" 
                                       id="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Descuento activo
                                </label>
                            </div>
                            <div class="form-text">Si está desactivado, el descuento no se aplicará</div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Botones -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('distributor-discounts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Descuento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountTypeSelect = document.getElementById('discount_type');
    const discountValueGroup = document.getElementById('discount_value_group');
    const giftProductsGroup = document.getElementById('gift_products_group');
    const discountValueInput = document.getElementById('discount_value');
    const discountValueHelp = document.getElementById('discount_value_help');
    const appliesToAllCheckbox = document.getElementById('applies_to_all_products');
    const specificProductGroup = document.getElementById('specific_product_group');

    // Manejar cambio de tipo de descuento
    discountTypeSelect.addEventListener('change', function() {
        const type = this.value;
        
        if (type === 'gift') {
            // Para regalos, ocultar el campo de valor y establecer automáticamente 100%
            discountValueGroup.style.display = 'none';
            giftProductsGroup.style.display = 'block';
            discountValueInput.required = false;
            discountValueInput.value = '100'; // Establecer automáticamente 100% de descuento
        } else {
            discountValueGroup.style.display = 'block';
            giftProductsGroup.style.display = 'none';
            discountValueInput.required = true;
            
            if (type === 'percentage') {
                discountValueHelp.textContent = 'Porcentaje de descuento (ej: 10 para 10%)';
                discountValueInput.max = '100';
            } else if (type === 'fixed_amount') {
                discountValueHelp.textContent = 'Monto fijo a descontar (ej: 500 para $500)';
                discountValueInput.removeAttribute('max');
            } else {
                discountValueHelp.textContent = '';
                discountValueInput.removeAttribute('max');
            }
        }
    });

    // Manejar checkbox de "aplicar a todos los productos"
    appliesToAllCheckbox.addEventListener('change', function() {
        if (this.checked) {
            specificProductGroup.style.display = 'none';
            // Limpiar campos específicos de producto
            // Limpiar el select múltiple de productos
            const invSelect = $('#supplier_inventory_ids');
            invSelect.val(null).trigger('change');
        } else {
            specificProductGroup.style.display = 'block';
        }
    });

    // Ejecutar al cargar la página para mantener estado
    discountTypeSelect.dispatchEvent(new Event('change'));
    appliesToAllCheckbox.dispatchEvent(new Event('change'));
});

</script>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select2 como en categorías
    $('#distributor_client_ids').select2({
        placeholder: 'Buscar distribuidor...',
        allowClear: true,
        language: {
            noResults: function() { return "No se encontraron resultados"; },
            searching: function() { return "Buscando..."; }
        }
    });

    $('#supplier_inventory_ids').select2({
        placeholder: 'Buscar producto del inventario...',
        allowClear: true,
        language: {
            noResults: function() { return "No se encontraron resultados"; },
            searching: function() { return "Buscando..."; }
        }
    });

});
</script>
@endpush