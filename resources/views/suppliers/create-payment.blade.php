@extends('layouts.app')

@section('title', 'Registrar Pago - ' . $supplier->name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave"></i> Registrar Pago - {{ $supplier->name }}
                    </h5>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <!-- Saldo Actual del Proveedor -->
                    <div class="alert {{ $currentBalance > 0 ? 'alert-warning' : ($currentBalance < 0 ? 'alert-success' : 'alert-info') }} mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle"></i> Estado de Cuenta Actual
                        </h6>
                        <h4 class="{{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-dark') }} mb-2">
                            {{ $formattedBalance }}
                        </h4>
                        @if($currentBalance > 0)
                            <p class="mb-0">Tenés una deuda pendiente con este proveedor.</p>
                        @elseif($currentBalance < 0)
                            <p class="mb-0">Tenés un excedente (saldo a favor) con este proveedor.</p>
                        @else
                            <p class="mb-0">La cuenta está al día con este proveedor.</p>
                        @endif
                    </div>

                    <form action="{{ route('suppliers.store-payment', $supplier) }}" method="POST">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-money-bill-wave"></i> Datos del Pago
                                </h6>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_date" class="form-label">Fecha del Pago *</label>
                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                       id="payment_date" name="payment_date"
                                       value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Monto del Pago *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount"
                                           value="{{ old('amount') }}" required min="0.01">
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="reference" class="form-label">Referencia (opcional)</label>
                                <input type="text" class="form-control @error('reference') is-invalid @enderror"
                                       id="reference" name="reference"
                                       value="{{ old('reference') }}"
                                       placeholder="N° de recibo, transferencia, etc.">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="balance_after" class="form-label">Saldo después del pago</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="balance_after" readonly>
                                </div>
                                <small class="text-muted">Se calcula automáticamente</small>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="observations" class="form-label">Observaciones (opcional)</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror"
                                          id="observations" name="observations" rows="3"
                                          placeholder="Observaciones adicionales sobre el pago...">{{ old('observations') }}</textarea>
                                @error('observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Alerta de excedente -->
                            <div class="col-12" id="excedente-alert" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Nota:</strong> El pago excede la deuda actual. Se generará un <strong>excedente (saldo a favor)</strong> de
                                    <span id="excedente-amount" class="fw-bold text-success"></span>.
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Registrar Pago
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
    const amountInput = document.getElementById('amount');
    const balanceAfter = document.getElementById('balance_after');
    const excedenteAlert = document.getElementById('excedente-alert');
    const excedenteAmount = document.getElementById('excedente-amount');
    const currentBalance = {{ $currentBalance }};

    function calculateBalance() {
        const payment = parseFloat(amountInput.value) || 0;
        const newBalance = currentBalance - payment;

        if (newBalance < 0) {
            balanceAfter.value = '$' + Math.abs(newBalance).toFixed(2) + ' (Excedente)';
            balanceAfter.classList.remove('text-danger', 'text-dark');
            balanceAfter.classList.add('text-success');
            excedenteAlert.style.display = 'block';
            excedenteAmount.textContent = '$' + Math.abs(newBalance).toFixed(2);
        } else if (newBalance > 0) {
            balanceAfter.value = '$' + newBalance.toFixed(2) + ' (Debe)';
            balanceAfter.classList.remove('text-success', 'text-dark');
            balanceAfter.classList.add('text-danger');
            excedenteAlert.style.display = 'none';
        } else {
            balanceAfter.value = '$0.00 (Al día)';
            balanceAfter.classList.remove('text-success', 'text-danger');
            balanceAfter.classList.add('text-dark');
            excedenteAlert.style.display = 'none';
        }
    }

    amountInput.addEventListener('input', calculateBalance);
    calculateBalance();
});
</script>
@endsection
