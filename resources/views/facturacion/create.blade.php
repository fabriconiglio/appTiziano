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
                                        <label for="distributor_client_id">Cliente *</label>
                                        <select name="distributor_client_id" id="distributor_client_id" 
                                                class="form-control @error('distributor_client_id') is-invalid @enderror" required>
                                            <option value="">Seleccionar cliente</option>
                                            @foreach($clients as $client)
                                            <option value="{{ $client->id }}" 
                                                    data-name="{{ $client->name }}"
                                                    data-surname="{{ $client->surname }}"
                                                    data-dni="{{ $client->dni }}"
                                                    data-email="{{ $client->email }}"
                                                    data-phone="{{ $client->phone }}"
                                                    data-domicilio="{{ $client->domicilio ?? '' }}">
                                                {{ $client->full_name }} - {{ $client->dni }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('distributor_client_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-group">
                                        <label for="technical_record_id">Compra (Ficha Técnica) *</label>
                                        <select name="technical_record_id" id="technical_record_id" 
                                                class="form-control @error('technical_record_id') is-invalid @enderror" required>
                                            <option value="">Primero seleccione un cliente</option>
                                        </select>
                                        <small class="form-text text-muted">Los productos se cargan automáticamente al seleccionar una compra</small>
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
                                            <th width="40%">Producto</th>
                                            <th width="15%">Cantidad</th>
                                            <th width="15%">Precio Unit.</th>
                                            <th width="15%">Subtotal</th>
                                            <th width="15%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="items-tbody">
                                        <!-- Los items se agregarán dinámicamente -->
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                            <td><strong id="subtotal-total">$0,00</strong></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>IVA (21%):</strong></td>
                                            <td><strong id="tax-total">$0,00</strong></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
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

@push('scripts')
<script>
let itemCounter = 0;

// Mostrar información del cliente y cargar compras
document.getElementById('distributor_client_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const clientInfo = document.getElementById('client-info');
    const technicalRecordSelect = document.getElementById('technical_record_id');
    
    if (this.value) {
        // Mostrar información del cliente
        document.getElementById('client-name').textContent = option.dataset.name;
        document.getElementById('client-surname').textContent = option.dataset.surname;
        document.getElementById('client-dni').textContent = option.dataset.dni;
        document.getElementById('client-email').textContent = option.dataset.email;
        document.getElementById('client-phone').textContent = option.dataset.phone;
        document.getElementById('client-domicilio').textContent = option.dataset.domicilio;
        clientInfo.style.display = 'block';
        
        // Cargar compras del cliente
        loadClientPurchases(this.value);
    } else {
        clientInfo.style.display = 'none';
        technicalRecordSelect.innerHTML = '<option value="">Primero seleccione un cliente</option>';
    }
});

// Cargar compras del cliente
function loadClientPurchases(clientId) {
    const technicalRecordSelect = document.getElementById('technical_record_id');
    technicalRecordSelect.innerHTML = '<option value="">Cargando compras...</option>';
    
    fetch(`/facturacion/clients/${clientId}/purchases`, {
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
                option.textContent = `FT-${purchase.id} - ${purchase.purchase_date} - $${purchase.total_amount}`;
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
    
    fetch(`/facturacion/technical-records/${technicalRecordId}/products`, {
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

// Agregar item
document.getElementById('add-item').addEventListener('click', function() {
    $('#productModal').modal('show');
});

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

// Buscar productos
document.getElementById('product-search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#products-list tr');
    
    rows.forEach(row => {
        const productName = row.querySelector('strong').textContent.toLowerCase();
        row.style.display = productName.includes(searchTerm) ? '' : 'none';
    });
});

function addItemToTable(productId, productName, productPrice, quantity = 1) {
    itemCounter++;
    
    // Convertir precio a número si viene como string
    const price = parseFloat(productPrice) || 0;
    const qty = parseFloat(quantity) || 1;
    
    const tbody = document.getElementById('items-tbody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <input type="hidden" name="items[${itemCounter}][product_id]" value="${productId}">
            <strong>${productName}</strong>
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
    
    document.querySelectorAll('#items-tbody tr').forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const itemSubtotal = quantity * price;
        
        row.querySelector('.item-subtotal').textContent = '$' + itemSubtotal.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        
        subtotal += itemSubtotal;
    });
    
    const tax = subtotal * 0.21;
    const total = subtotal + tax;
    
    document.getElementById('subtotal-total').textContent = '$' + subtotal.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    document.getElementById('tax-total').textContent = '$' + tax.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    document.getElementById('grand-total').textContent = '$' + total.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Validación del formulario
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('#items-tbody tr');
    if (items.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un producto a la factura');
        return false;
    }
});
</script>
@endpush
