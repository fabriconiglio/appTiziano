@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Nuevo Aumento de Costos</span>
                        <a href="{{ route('cost-increases.index') }}" class="btn btn-secondary btn-sm">
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

                        <form action="{{ route('cost-increases.preview') }}" method="POST" id="increaseForm">
                            @csrf

                            <div class="row mb-4">
                                <h5>Configuración del Aumento</h5>
                                <hr>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Aumento *</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type" id="type_porcentual" value="porcentual" {{ old('type', 'porcentual') == 'porcentual' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="type_porcentual">
                                            Porcentual (%)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type" id="type_fijo" value="fijo" {{ old('type') == 'fijo' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="type_fijo">
                                            Monto Fijo ($)
                                        </label>
                                    </div>
                                    @error('type')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="increase_value" class="form-label">Valor del Aumento *</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="value_prefix">%</span>
                                        <input type="number" step="0.01" min="0.01" class="form-control @error('increase_value') is-invalid @enderror" 
                                               id="increase_value" name="increase_value" value="{{ old('increase_value') }}" required>
                                        @error('increase_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted" id="value_hint">Ingrese el porcentaje (máximo 100%)</small>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Alcance del Aumento *</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="scope_type" id="scope_producto" value="producto" {{ old('scope_type', 'producto') == 'producto' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="scope_producto">
                                            Producto Individual
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="scope_type" id="scope_marca" value="marca" {{ old('scope_type') == 'marca' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="scope_marca">
                                            Por Marca (todos los productos de una marca)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="scope_type" id="scope_multiples" value="multiples" {{ old('scope_type') == 'multiples' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="scope_multiples">
                                            Varios Productos (de distintas marcas)
                                        </label>
                                    </div>
                                    @error('scope_type')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3" id="product_select_container">
                                    <label for="supplier_inventory_id" class="form-label">Seleccionar Producto *</label>
                                    <select class="form-select @error('supplier_inventory_id') is-invalid @enderror" 
                                            id="supplier_inventory_id" name="supplier_inventory_id">
                                        <option value="">Buscar producto...</option>
                                    </select>
                                    @error('supplier_inventory_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3" id="brand_select_container" style="display: none;">
                                    <label for="distributor_brand_id" class="form-label">Seleccionar Marca *</label>
                                    <select class="form-select @error('distributor_brand_id') is-invalid @enderror" 
                                            id="distributor_brand_id" name="distributor_brand_id">
                                        <option value="">Seleccione una marca</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('distributor_brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('distributor_brand_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3" id="multiples_select_container" style="display: none;">
                                    <label for="supplier_inventory_ids" class="form-label">Seleccionar Productos *</label>
                                    <select class="form-select @error('supplier_inventory_ids') is-invalid @enderror" 
                                            id="supplier_inventory_ids" name="supplier_inventory_ids[]" multiple>
                                    </select>
                                    @error('supplier_inventory_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Puede seleccionar múltiples productos de distintas marcas</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('cost-increases.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i> Ver Vista Previa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                // Configurar Select2 para búsqueda de productos individuales
                $('#supplier_inventory_id').select2({
                    placeholder: 'Buscar producto...',
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
                        processResults: function(data) {
                            return {
                                results: data.map(function(item) {
                                    let displayText = item.display_text || item.product_name;
                                    if (item.brand) {
                                        displayText += ' - ' + item.brand;
                                    }
                                    return {
                                        id: item.id,
                                        text: displayText
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });

                // Configurar Select2 para selección múltiple de productos
                $('#supplier_inventory_ids').select2({
                    placeholder: 'Buscar y seleccionar productos...',
                    allowClear: true,
                    multiple: true,
                    tags: false,
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
                        processResults: function(data) {
                            return {
                                results: data.map(function(item) {
                                    let displayText = item.display_text || item.product_name;
                                    if (item.brand) {
                                        displayText += ' - ' + item.brand;
                                    }
                                    return {
                                        id: item.id,
                                        text: displayText
                                    };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });

                // Manejar cambio de tipo de aumento
                $('input[name="type"]').on('change', function() {
                    const type = $(this).val();
                    const prefix = $('#value_prefix');
                    const hint = $('#value_hint');
                    
                    if (type === 'porcentual') {
                        prefix.text('%');
                        hint.text('Ingrese el porcentaje (máximo 100%)');
                        $('#increase_value').attr('max', '100');
                    } else {
                        prefix.text('$');
                        hint.text('Ingrese el monto fijo a agregar');
                        $('#increase_value').removeAttr('max');
                    }
                });

                // Manejar cambio de alcance
                $('input[name="scope_type"]').on('change', function() {
                    const scopeType = $(this).val();
                    if (scopeType === 'producto') {
                        $('#product_select_container').show();
                        $('#brand_select_container').hide();
                        $('#multiples_select_container').hide();
                        $('#supplier_inventory_id').prop('required', true);
                        $('#distributor_brand_id').prop('required', false).val('');
                        $('#supplier_inventory_ids').prop('required', false).val(null).trigger('change');
                    } else if (scopeType === 'marca') {
                        $('#product_select_container').hide();
                        $('#brand_select_container').show();
                        $('#multiples_select_container').hide();
                        $('#supplier_inventory_id').prop('required', false).val(null).trigger('change');
                        $('#distributor_brand_id').prop('required', true);
                        $('#supplier_inventory_ids').prop('required', false).val(null).trigger('change');
                    } else {
                        // multiples
                        $('#product_select_container').hide();
                        $('#brand_select_container').hide();
                        $('#multiples_select_container').show();
                        $('#supplier_inventory_id').prop('required', false).val(null).trigger('change');
                        $('#distributor_brand_id').prop('required', false).val('');
                        $('#supplier_inventory_ids').prop('required', true);
                    }
                });

                // Inicializar según valores antiguos
                const scopeType = $('input[name="scope_type"]:checked').val();
                if (scopeType === 'marca') {
                    $('#product_select_container').hide();
                    $('#brand_select_container').show();
                    $('#multiples_select_container').hide();
                    $('#supplier_inventory_id').prop('required', false).val('');
                    $('#distributor_brand_id').prop('required', true);
                } else if (scopeType === 'multiples') {
                    $('#product_select_container').hide();
                    $('#brand_select_container').hide();
                    $('#multiples_select_container').show();
                    $('#supplier_inventory_id').prop('required', false).val('');
                    $('#distributor_brand_id').prop('required', false).val('');
                    $('#supplier_inventory_ids').prop('required', true);
                } else {
                    $('#supplier_inventory_id').prop('required', true);
                    $('#distributor_brand_id').prop('required', false).val('');
                    $('#supplier_inventory_ids').prop('required', false).val('');
                }
            });
        </script>
    @endpush
@endsection

