@extends('layouts.app')

@section('title', 'Agregar Compra - ' . $supplier->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-plus"></i> Agregar Compra - {{ $supplier->name }}
                    </h5>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <!-- Información de Crédito Disponible -->
                    @if($supplier->available_credit > 0)
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle"></i> Crédito Disponible
                            </h6>
                            <p class="mb-2">Tienes un saldo a favor con este proveedor de:</p>
                            <h4 class="text-success mb-2">${{ number_format($supplier->available_credit, 2) }}</h4>
                            <p class="mb-0">Este crédito se aplicará automáticamente a la nueva compra. Puedes desactivar esta opción marcando la casilla correspondiente.</p>
                        </div>
                    @endif

                    <form action="{{ route('suppliers.store-purchase', $supplier) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Información de la Compra -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-shopping-cart"></i> Información de la Compra
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="supplier_name" class="form-label">Nombre del Proveedor</label>
                                <input type="text" class="form-control" id="supplier_name" 
                                       value="{{ $supplier->name }}" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="purchase_date" class="form-label">Fecha *</label>
                                <input type="date" class="form-control @error('purchase_date') is-invalid @enderror" 
                                       id="purchase_date" name="purchase_date" 
                                       value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="receipt_number" class="form-label">Número de Boleta *</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" 
                                           id="receipt_number" name="receipt_number" 
                                           value="{{ old('receipt_number') }}" required>
                                    <span class="input-group-text" id="receipt-search-spinner" style="display: none;">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </div>
                                <small class="text-muted" id="receipt-search-message"></small> 
                                @error('receipt_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="total_amount" class="form-label">Total de la Boleta *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('total_amount') is-invalid @enderror" 
                                           id="total_amount" name="total_amount" 
                                           value="{{ old('total_amount') }}" required>
                                </div>
                                @error('total_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_amount" class="form-label">Pago Realizado <span id="payment-required-indicator">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('payment_amount') is-invalid @enderror" 
                                           id="payment_amount" name="payment_amount" 
                                           value="{{ old('payment_amount') }}" required>
                                </div>
                                <div class="form-text" id="payment-help-text">
                                    Monto a pagar después de aplicar crédito disponible
                                </div>
                                @error('payment_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if($supplier->available_credit > 0)
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="use_available_credit" 
                                               name="use_available_credit" value="1" checked>
                                        <label class="form-check-label" for="use_available_credit">
                                            <strong>Usar crédito disponible</strong> (${{ number_format($supplier->available_credit, 2) }})
                                        </label>
                                        <div class="form-text">
                                            Si está marcado, el crédito disponible se aplicará automáticamente a esta compra.
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-6 mb-3">
                                <label for="balance_amount" class="form-label">Saldo Pendiente</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" 
                                           id="balance_amount" name="balance_amount" readonly>
                                </div>
                                <small class="text-muted">Se calcula automáticamente</small>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="receipt_file" class="form-label">Subir Boleta</label>
                                <input type="file" class="form-control @error('receipt_file') is-invalid @enderror" 
                                       id="receipt_file" name="receipt_file" 
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <small class="text-muted">Formatos permitidos: PDF, JPG, PNG, DOC, DOCX</small>
                                @error('receipt_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Observaciones adicionales sobre la compra...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Compra
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalAmount = document.getElementById('total_amount');
    const paymentAmount = document.getElementById('payment_amount');
    const balanceAmount = document.getElementById('balance_amount');
    const receiptNumber = document.getElementById('receipt_number');
    const receiptSearchSpinner = document.getElementById('receipt-search-spinner');
    const receiptSearchMessage = document.getElementById('receipt-search-message');
    const useCreditCheckbox = document.getElementById('use_available_credit');
    const paymentRequiredIndicator = document.getElementById('payment-required-indicator');
    const paymentHelpText = document.getElementById('payment-help-text');
    
    // URL para la búsqueda de boletas
    const searchUrl = '{{ route("suppliers.get-receipt-total", $supplier) }}';
    let searchTimeout;

    // Crédito disponible del proveedor
    const availableCredit = {{ $supplier->available_credit ?? 0 }};

    function calculateBalance() {
        const total = parseFloat(totalAmount.value) || 0;
        const payment = parseFloat(paymentAmount.value) || 0;
        const useCredit = useCreditCheckbox ? useCreditCheckbox.checked : false;
        
        let balance = total - payment;
        
        // Si el checkbox de crédito está marcado y hay crédito disponible, restarlo del balance
        if (useCredit && availableCredit > 0) {
            // Aplicar crédito disponible al balance pendiente
            balance = balance - availableCredit;
            // Asegurar que el balance no sea menor que 0
            if (balance < 0) {
                balance = 0;
            }
        }
        
        balanceAmount.value = balance.toFixed(2);
        
        // Actualizar indicadores de campo requerido
        const remainingAfterCredit = useCredit && availableCredit > 0 ? total - availableCredit : total;
        updatePaymentFieldRequirements(remainingAfterCredit);
    }

    function updatePaymentFieldRequirements(remainingAmount) {
        if (remainingAmount <= 0) {
            // No se requiere pago adicional
            paymentAmount.required = false;
            paymentRequiredIndicator.textContent = '';
            paymentHelpText.textContent = 'No se requiere pago adicional - el crédito cubre toda la compra';
            if (paymentAmount.value === '' || paymentAmount.value === '0') {
                paymentAmount.value = '0';
            }
        } else {
            // Se requiere pago adicional
            paymentAmount.required = true;
            paymentRequiredIndicator.textContent = '*';
            paymentHelpText.textContent = `Monto a pagar después de aplicar crédito disponible (restante: $${remainingAmount.toFixed(2)})`;
        }
    }

    function searchReceiptTotal(receiptNumberValue) {
        if (!receiptNumberValue || receiptNumberValue.length < 3) {
            receiptSearchMessage.textContent = '';
            return;
        }

        // Mostrar spinner
        receiptSearchSpinner.style.display = 'block';
        receiptSearchMessage.textContent = 'Buscando boleta...';

        fetch(`${searchUrl}?receipt_number=${encodeURIComponent(receiptNumberValue)}`)
            .then(response => response.json())
            .then(data => {
                receiptSearchSpinner.style.display = 'none';
                
                if (data.success) {
                    // Si hay saldo pendiente, usar ese valor; si no, usar el total
                    if (data.balance_amount > 0) {
                        totalAmount.value = data.balance_amount;
                        paymentAmount.value = data.payment_amount;
                        receiptSearchMessage.textContent = `✓ Boleta encontrada - Saldo pendiente: $${data.balance_amount} (${data.purchase_date})`;
                    } else {
                        totalAmount.value = data.total_amount;
                        receiptSearchMessage.textContent = `✓ Boleta encontrada - Total: $${data.total_amount} (${data.purchase_date})`;
                    }
                    receiptSearchMessage.className = 'text-success';
                    
                    // Recalcular el saldo
                    calculateBalance();
                } else {
                    receiptSearchMessage.textContent = data.message || 'Boleta no encontrada';
                    receiptSearchMessage.className = 'text-muted';
                }
            })
            .catch(error => {
                receiptSearchSpinner.style.display = 'none';
                receiptSearchMessage.textContent = 'Error al buscar la boleta';
                receiptSearchMessage.className = 'text-danger';
                console.error('Error:', error);
            });
    }

    // Event listeners
    totalAmount.addEventListener('input', calculateBalance);
    paymentAmount.addEventListener('input', calculateBalance);
    
    if (useCreditCheckbox) {
        useCreditCheckbox.addEventListener('change', calculateBalance);
    }
    
    // Búsqueda de boleta con debounce
    receiptNumber.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const value = this.value.trim();
        
        if (value) {
            searchTimeout = setTimeout(() => {
                searchReceiptTotal(value);
            }, 500); // Esperar 500ms después del último input
        } else {
            receiptSearchMessage.textContent = '';
            receiptSearchMessage.className = 'text-muted';
        }
    });
    
    // Calcular balance inicial
    calculateBalance();
});
</script>
@endsection 