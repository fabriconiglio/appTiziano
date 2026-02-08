@extends('layouts.app')

@section('title', 'Nueva Factura AFIP')

@section('content')
<div class="container-fluid">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-plus"></i> Nueva Factura AFIP</h1>
        <div>
            <a href="{{ route('facturacion.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <form action="{{ route('facturacion.store') }}" method="POST" id="invoiceForm">
                    @csrf
                    <div class="card-body">
                        <!-- Información básica -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-file-invoice me-2"></i>Información de la Factura
                            </h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="client_search">Cliente *</label>
                                        <select name="client_search" id="client_search" 
                                                class="form-control @error('client_type') is-invalid @enderror" required>
                                            <option value="">Buscar cliente...</option>
                                        </select>
                                        <!-- Campos ocultos para almacenar los datos del cliente seleccionado -->
                                        <input type="hidden" name="client_type" id="client_type">
                                        <input type="hidden" name="client_id" id="client_id">
                                        <input type="hidden" name="distributor_client_id" id="distributor_client_id">
                                        @error('client_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        @error('client_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="technical_record_id">Compra (Ficha Técnica) <span id="technical_record_required" style="display:none;">*</span></label>
                                        <select name="technical_record_id" id="technical_record_id" 
                                                class="form-control @error('technical_record_id') is-invalid @enderror">
                                            <option value="">Primero seleccione un cliente</option>
                                        </select>
                                        <small class="form-text text-muted">Los productos se cargan automáticamente al seleccionar una compra</small>
                                        <small class="form-text text-info" id="no_frecuente_message" style="display:none;">
                                            Los clientes no frecuentes no requieren seleccionar una compra
                                        </small>
                                        @error('technical_record_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-group">
                                        <label for="invoice_type">Tipo de Factura *</label>
                                        <select name="invoice_type" id="invoice_type" 
                                                class="form-control @error('invoice_type') is-invalid @enderror" required>
                                            <option value="">Seleccionar tipo</option>
                                            <option value="A" {{ old('invoice_type') == 'A' ? 'selected' : '' }}>Factura A</option>
                                            <option value="B" {{ old('invoice_type', 'B') == 'B' ? 'selected' : '' }}>Factura B</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            <strong>A:</strong> Cliente Resp. Inscripto | <strong>B:</strong> Consumidor Final
                                        </small>
                                        @error('invoice_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <div class="form-group">
                                        <label for="invoice_date">Fecha *</label>
                                        <input type="date" name="invoice_date" id="invoice_date" 
                                               class="form-control @error('invoice_date') is-invalid @enderror" 
                                               value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                                        @error('invoice_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Checkbox Consumidor Final (solo para Factura B) -->
                            <div class="row" id="consumidor-final-row" style="display: none;">
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="consumidor_final" name="consumidor_final">
                                        <label class="form-check-label" for="consumidor_final">
                                            <strong>Consumidor Final</strong> (sin datos de cliente)
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Marcar esta opción para crear la factura sin asociar un cliente. Se registrará como "Consumidor Final".
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del cliente -->
                        <div class="mb-4" id="client-info" style="display: none;">
                            <div class="alert alert-info">
                                <h6 class="mb-3">
                                    <i class="fas fa-user me-2"></i>Información del Cliente
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Nombre:</strong> <span id="client-name"></span><br>
                                        <strong>Apellido:</strong> <span id="client-surname"></span><br>
                                        <strong>DNI:</strong> <span id="client-dni"></span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Email:</strong> <span id="client-email"></span><br>
                                        <strong>Teléfono:</strong> <span id="client-phone"></span><br>
                                        <strong>Domicilio:</strong> <span id="client-domicilio"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items de la factura -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-list me-2"></i>Productos
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="items-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30%">Producto</th>
                                            <th width="12%">Cantidad</th>
                                            <th width="15%">Precio Unit.</th>
                                            <th width="13%">IVA</th>
                                            <th width="15%">Subtotal</th>
                                            <th width="15%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody">
                                        <!-- Los items se agregarán dinámicamente -->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>Neto (Base Imponible):</strong></td>
                                            <td><strong id="neto-total">$0,00</strong></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="text-end"><strong>IVA 21%:</strong></td>
                                            <td><strong id="iva-total">$0,00</strong></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                            <td><strong id="grand-total">$0,00</strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <div class="alert alert-light border mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    Los productos se cargan automáticamente al seleccionar una compra.
                                </small>
                            </div>
                            
                            <!-- Botón para agregar producto/servicio manualmente (Consumidor Final) -->
                            <div id="manual-item-section" style="display: none;" class="mt-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="fas fa-plus-circle me-1"></i> Agregar Producto/Servicio</h6>
                                        <div class="row">
                                            <div class="col-md-5 mb-2">
                                                <input type="text" id="manual-product-name" class="form-control form-control-sm" placeholder="Descripción del producto/servicio">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <input type="number" id="manual-product-qty" class="form-control form-control-sm" placeholder="Cantidad" value="1" min="1">
                                            </div>
                                            <div class="col-md-3 mb-2">
                                                <input type="number" id="manual-product-price" class="form-control form-control-sm" placeholder="Precio unitario" step="0.01" min="0">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <button type="button" id="add-manual-item" class="btn btn-primary btn-sm w-100">
                                                    <i class="fas fa-plus me-1"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="mb-3">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-sticky-note me-2"></i>Notas Adicionales
                            </h5>
                            <div class="form-group">
                                <label for="notes">Notas</label>
                                <textarea name="notes" id="notes" rows="3" 
                                          class="form-control @error('notes') is-invalid @enderror" 
                                          placeholder="Notas adicionales para la factura">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light py-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Crear Factura
                            </button>
                            <a href="{{ route('facturacion.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let itemCounter = 0;
let selectedClientData = null;

// Inicializar Select2 para búsqueda de clientes
$(document).ready(function() {
    $('#client_search').select2({
        placeholder: 'Buscar cliente por nombre, DNI, email...',
        allowClear: true,
        ajax: {
            url: '{{ route("facturacion.clients.search") }}',
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
                        return {
                            id: item.id,
                            text: item.text,
                            client_type: item.client_type,
                            client_id: item.client_id,
                            name: item.name,
                            surname: item.surname,
                            dni: item.dni,
                            email: item.email,
                            phone: item.phone,
                            domicilio: item.domicilio
                        };
                    })
                };
            },
            cache: true
        },
        minimumInputLength: 2
    });

    // Manejar selección de cliente
    $('#client_search').on('select2:select', function(e) {
        const data = e.params.data;
        selectedClientData = data;
        
        // Llenar campos ocultos
        $('#client_type').val(data.client_type);
        $('#client_id').val(data.client_id);
        
        // Si es cliente de distribuidora, también llenar distributor_client_id
        if (data.client_type === 'distributor_client') {
            $('#distributor_client_id').val(data.client_id);
        } else {
            $('#distributor_client_id').val('');
        }
        
        // Mostrar información del cliente
        document.getElementById('client-name').textContent = data.name || '';
        document.getElementById('client-surname').textContent = data.surname || '';
        document.getElementById('client-dni').textContent = data.dni || 'No especificado';
        document.getElementById('client-email').textContent = data.email || 'No especificado';
        document.getElementById('client-phone').textContent = data.phone || 'No especificado';
        document.getElementById('client-domicilio').textContent = data.domicilio || 'No especificado';
        document.getElementById('client-info').style.display = 'block';
        
        // Cargar compras del cliente
        loadClientPurchases(data.client_id, data.client_type);
        
        // Si es cliente no frecuente, seleccionar automáticamente su compra
        if (data.client_type === 'distributor_no_frecuente' || data.client_type === 'client_no_frecuente') {
            // Esperar a que se carguen las compras y seleccionar automáticamente
            setTimeout(() => {
                const technicalRecordSelect = document.getElementById('technical_record_id');
                if (technicalRecordSelect.options.length > 1) {
                    technicalRecordSelect.value = data.client_id;
                    technicalRecordSelect.dispatchEvent(new Event('change'));
                }
            }, 500);
        }
    });

    // Limpiar cuando se deselecciona
    $('#client_search').on('select2:clear', function() {
        selectedClientData = null;
        $('#client_type').val('');
        $('#client_id').val('');
        $('#distributor_client_id').val('');
        document.getElementById('client-info').style.display = 'none';
        document.getElementById('technical_record_id').innerHTML = '<option value="">Primero seleccione un cliente</option>';
        clearProductsTable();
    });
});

// Cargar compras del cliente
function loadClientPurchases(clientId, clientType) {
    const technicalRecordSelect = document.getElementById('technical_record_id');
    technicalRecordSelect.innerHTML = '<option value="">Cargando compras...</option>';
    
    // Los clientes no frecuentes tienen su compra directamente en el registro
    // Mostrar mensaje informativo pero permitir seleccionar
    if (clientType === 'distributor_no_frecuente' || clientType === 'client_no_frecuente') {
        document.getElementById('no_frecuente_message').style.display = 'block';
    } else {
        document.getElementById('no_frecuente_message').style.display = 'none';
    }
    
    technicalRecordSelect.disabled = false;
    technicalRecordSelect.setAttribute('required', 'required');
    document.getElementById('technical_record_required').style.display = 'inline';
    
    const url = `/facturacion/clients/${clientId}/purchases?client_type=${clientType}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        technicalRecordSelect.innerHTML = '<option value="">Seleccionar compra</option>';
        
        if (data.length === 0) {
            technicalRecordSelect.innerHTML += '<option value="" disabled>No hay compras registradas</option>';
            return;
        }
        
            data.forEach(purchase => {
                const option = document.createElement('option');
                option.value = purchase.id;
                // Para clientes no frecuentes, mostrar texto diferente
                if (clientType === 'distributor_no_frecuente' || clientType === 'client_no_frecuente') {
                    option.textContent = `Compra - ${purchase.purchase_date} - $${purchase.total_amount}`;
                } else {
                    option.textContent = `FT-${purchase.id} - ${purchase.purchase_date} - $${purchase.total_amount}`;
                }
                technicalRecordSelect.appendChild(option);
            });
    })
    .catch(error => {
        console.error('Error:', error);
        technicalRecordSelect.innerHTML = '<option value="">Error al cargar compras</option>';
        alert('Error al cargar las compras del cliente. Verifique la consola para más detalles.');
    });
}

// Cargar productos de la compra seleccionada
document.getElementById('technical_record_id').addEventListener('change', function() {
    if (this.value) {
        loadPurchaseProducts(this.value);
    } else {
        clearProductsTable();
    }
});

// Cargar productos de la compra
function loadPurchaseProducts(technicalRecordId) {
    // Limpiar tabla actual
    clearProductsTable();
    
    // Obtener el tipo de cliente para saber qué tipo de ficha técnica buscar
    const clientType = document.getElementById('client_type').value;
    
    const url = `/facturacion/technical-records/${technicalRecordId}/products?client_type=${clientType}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
        .then(data => {
            if (data.length === 0) {
                alert('No se encontraron productos en esta compra');
                return;
            }
            
            // Agregar cada producto a la tabla
            data.forEach(product => {
                addItemToTable(
                    product.product_id,
                    product.product_name,
                    parseFloat(product.unit_price),
                    parseInt(product.quantity)
                );
            });
            
            // Si no hay productos y es cliente de peluquería, mostrar mensaje informativo
            if (data.length === 0) {
                const clientType = document.getElementById('client_type').value;
                if (clientType === 'client') {
                    alert('Esta ficha técnica no tiene productos asociados. Puedes agregar el servicio manualmente.');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los productos de la compra: ' + error.message);
        });
}

// Limpiar tabla de productos
function clearProductsTable() {
    const tbody = document.getElementById('items-tbody');
    tbody.innerHTML = '';
    itemCounter = 0;
    updateTotals();
}

// Agregar item (si existe el botón add-item)
if (document.getElementById('add-item')) {
    document.getElementById('add-item').addEventListener('click', function() {
        $('#productModal').modal('show');
    });
}

// Seleccionar producto
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('select-product')) {
        const productId = e.target.dataset.productId;
        const productName = e.target.dataset.productName;
        const productPrice = parseFloat(e.target.dataset.productPrice);
        
        addItemToTable(productId, productName, productPrice);
        $('#productModal').modal('hide');
    }
});

// Buscar productos (si existe el campo product-search)
if (document.getElementById('product-search')) {
    document.getElementById('product-search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#products-list tr');
        
        rows.forEach(row => {
            const productName = row.querySelector('strong').textContent.toLowerCase();
            row.style.display = productName.includes(searchTerm) ? '' : 'none';
        });
    });
}

function addItemToTable(productId, productName, productPrice, quantity = 1) {
    itemCounter++;
    
    // Convertir precio a número si viene como string
    const price = parseFloat(productPrice) || 0;
    const qty = parseFloat(quantity) || 1;
    
    const tbody = document.getElementById('items-tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <input type="hidden" name="items[${itemCounter}][product_id]" value="${productId || ''}">
            <input type="text" name="items[${itemCounter}][product_name]" 
                   class="form-control form-control-sm" 
                   value="${productName}" required>
        </td>
        <td>
            <input type="number" name="items[${itemCounter}][quantity]" 
                   class="form-control form-control-sm item-quantity" 
                   value="${qty}" min="1" required>
        </td>
        <td>
            <input type="number" name="items[${itemCounter}][unit_price]" 
                   class="form-control form-control-sm item-price" 
                   value="${price.toFixed(2)}" 
                   step="0.01" min="0" required>
        </td>
        <td class="item-iva">$0,00</td>
        <td class="item-subtotal">$0,00</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    updateTotals();
    
    // Event listeners para el nuevo item
    row.querySelector('.item-quantity').addEventListener('input', updateTotals);
    row.querySelector('.item-price').addEventListener('input', updateTotals);
    row.querySelector('.remove-item').addEventListener('click', function() {
        row.remove();
        updateTotals();
    });
}

function updateTotals() {
    let subtotal = 0;
    let taxAmount = 0;
    
    document.querySelectorAll('#items-tbody tr').forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const itemSubtotal = quantity * price;
        
        // Calcular IVA interno del precio que ya lo incluye
        // Base imponible = precio / 1.21
        // IVA = base imponible × 0.21
        const baseImponible = itemSubtotal / 1.21;
        const itemTax = baseImponible * 0.21;
        
        row.querySelector('.item-iva').textContent = '$' + itemTax.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        row.querySelector('.item-subtotal').textContent = '$' + itemSubtotal.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        subtotal += itemSubtotal;
        taxAmount += itemTax;
    });
    
    // El total es igual al subtotal porque el IVA ya está incluido en los precios
    const total = subtotal;
    const neto = subtotal / 1.21;
    
    document.getElementById('neto-total').textContent = '$' + neto.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    document.getElementById('iva-total').textContent = '$' + taxAmount.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    document.getElementById('grand-total').textContent = '$' + total.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Manejar cambio de tipo de factura (mostrar/ocultar checkbox consumidor final)
document.getElementById('invoice_type').addEventListener('change', function() {
    const consumidorFinalRow = document.getElementById('consumidor-final-row');
    const consumidorFinalCheckbox = document.getElementById('consumidor_final');
    
    if (this.value === 'B') {
        consumidorFinalRow.style.display = 'block';
    } else {
        consumidorFinalRow.style.display = 'none';
        // Desmarcar si no es factura B
        if (consumidorFinalCheckbox.checked) {
            consumidorFinalCheckbox.checked = false;
            consumidorFinalCheckbox.dispatchEvent(new Event('change'));
        }
    }
});

// Disparar evento change para inicializar el estado del checkbox
document.getElementById('invoice_type').dispatchEvent(new Event('change'));

// Manejar checkbox de Consumidor Final
document.getElementById('consumidor_final').addEventListener('change', function() {
    const isConsumidorFinal = this.checked;
    const clientSearchContainer = document.getElementById('client_search').closest('.col-md-4');
    const technicalRecordContainer = document.getElementById('technical_record_id').closest('.col-md-4');
    const clientInfo = document.getElementById('client-info');
    const manualItemSection = document.getElementById('manual-item-section');
    
    if (isConsumidorFinal) {
        // Ocultar selectores de cliente y compra
        clientSearchContainer.style.display = 'none';
        technicalRecordContainer.style.display = 'none';
        clientInfo.style.display = 'none';
        
        // Setear tipo de cliente como consumidor_final
        document.getElementById('client_type').value = 'consumidor_final';
        document.getElementById('client_id').value = '';
        document.getElementById('distributor_client_id').value = '';
        
        // Limpiar selección de cliente
        $('#client_search').val(null).trigger('change');
        document.getElementById('technical_record_id').innerHTML = '<option value="">No aplica</option>';
        
        // Limpiar tabla de productos
        clearProductsTable();
        
        // Mostrar sección de agregar producto manual
        manualItemSection.style.display = 'block';
    } else {
        // Mostrar selectores
        clientSearchContainer.style.display = 'block';
        technicalRecordContainer.style.display = 'block';
        
        // Resetear tipo de cliente
        document.getElementById('client_type').value = '';
        document.getElementById('client_id').value = '';
        
        // Ocultar sección manual
        manualItemSection.style.display = 'none';
        
        // Limpiar tabla
        clearProductsTable();
        document.getElementById('technical_record_id').innerHTML = '<option value="">Primero seleccione un cliente</option>';
    }
});

// Agregar producto/servicio manualmente (Consumidor Final)
document.getElementById('add-manual-item').addEventListener('click', function() {
    const name = document.getElementById('manual-product-name').value.trim();
    const qty = parseInt(document.getElementById('manual-product-qty').value) || 1;
    const price = parseFloat(document.getElementById('manual-product-price').value) || 0;
    
    if (!name) {
        alert('Ingrese la descripción del producto/servicio');
        return;
    }
    if (price <= 0) {
        alert('Ingrese un precio válido');
        return;
    }
    
    addItemToTable(null, name, price, qty);
    
    // Limpiar campos
    document.getElementById('manual-product-name').value = '';
    document.getElementById('manual-product-qty').value = '1';
    document.getElementById('manual-product-price').value = '';
});

// Validación del formulario
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    const clientType = document.getElementById('client_type').value;
    
    // Si es consumidor final, no validar cliente
    if (clientType === 'consumidor_final') {
        // Solo validar que haya al menos un producto
        const items = document.querySelectorAll('#items-tbody tr');
        if (items.length === 0) {
            e.preventDefault();
            alert('Debe agregar al menos un producto a la factura');
            return false;
        }
        return true;
    }
    
    // Validar que se haya seleccionado un cliente
    if (!clientType || !document.getElementById('client_id').value) {
        e.preventDefault();
        alert('Debe seleccionar un cliente');
        return false;
    }
    
    // Validar technical_record_id solo si no es cliente no frecuente
    const technicalRecordId = document.getElementById('technical_record_id').value;
    
    if (!['distributor_no_frecuente', 'client_no_frecuente'].includes(clientType) && !technicalRecordId) {
        e.preventDefault();
        alert('Debe seleccionar una compra (ficha técnica)');
        return false;
    }
    
    // Validar que haya al menos un producto
    const items = document.querySelectorAll('#items-tbody tr');
    if (items.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un producto a la factura');
        return false;
    }
});
</script>
@endpush
