@extends('layouts.app')

@section('title', 'Nueva Factura AFIP')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> Nueva Factura AFIP
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('facturacion.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <form action="{{ route('facturacion.store') }}" method="POST" id="invoiceForm">
                    @csrf
                    <div class="card-body">
                        <!-- Información básica -->
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="invoice_type">Tipo de Factura *</label>
                                    <select name="invoice_type" id="invoice_type" 
                                            class="form-control @error('invoice_type') is-invalid @enderror" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="A" {{ old('invoice_type') == 'A' ? 'selected' : '' }}>Factura A</option>
                                        <option value="B" {{ old('invoice_type') == 'B' ? 'selected' : '' }}>Factura B</option>
                                        <option value="C" {{ old('invoice_type') == 'C' ? 'selected' : '' }}>Factura C</option>
                                    </select>
                                    @error('invoice_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
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

                        <!-- Información del cliente -->
                        <div class="row" id="client-info" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-user"></i> Información del Cliente</h6>
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
                        </div>

                        <!-- Items de la factura -->
                        <div class="row">
                            <div class="col-12">
                                <h5><i class="fas fa-list"></i> Productos</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="items-table">
                                        <thead>
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
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                                <td><strong id="subtotal-total">$0,00</strong></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>IVA (21%):</strong></td>
                                                <td><strong id="tax-total">$0,00</strong></td>
                                                <td></td>
                                            </tr>
                                            <tr class="table-primary">
                                                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                                <td><strong id="grand-total">$0,00</strong></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <button type="button" class="btn btn-success btn-sm" id="add-item">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="row mt-3">
                            <div class="col-12">
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
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Crear Factura
                        </button>
                        <a href="{{ route('facturacion.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para seleccionar producto -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar Producto</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="product-search">Buscar Producto</label>
                    <input type="text" id="product-search" class="form-control" placeholder="Nombre del producto">
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="products-list">
                            @foreach($products as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product->name }}</strong><br>
                                    <small class="text-muted">{{ $product->description }}</small>
                                </td>
                                <td>${{ number_format($product->price, 2, ',', '.') }}</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm select-product" 
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            data-product-price="{{ $product->price }}">
                                        Seleccionar
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let itemCounter = 0;

// Mostrar información del cliente
document.getElementById('distributor_client_id').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const clientInfo = document.getElementById('client-info');
    
    if (this.value) {
        document.getElementById('client-name').textContent = option.dataset.name;
        document.getElementById('client-surname').textContent = option.dataset.surname;
        document.getElementById('client-dni').textContent = option.dataset.dni;
        document.getElementById('client-email').textContent = option.dataset.email;
        document.getElementById('client-phone').textContent = option.dataset.phone;
        document.getElementById('client-domicilio').textContent = option.dataset.domicilio;
        clientInfo.style.display = 'block';
    } else {
        clientInfo.style.display = 'none';
    }
});

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

function addItemToTable(productId, productName, productPrice) {
    itemCounter++;
    
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
                   value="1" min="1" required>
        </td>
        <td>
            <input type="number" name="items[${itemCounter}][unit_price]" 
                   class="form-control form-control-sm item-price" 
                   value="${productPrice.toFixed(2)}" 
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
