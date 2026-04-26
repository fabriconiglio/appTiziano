@extends('layouts.app')

@section('title', 'Cuenta Corriente - ' . $hairdressingSupplier->full_name)

@section('content')
<div class="container">
    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-invoice-dollar"></i> Cuenta Corriente</h1>
        <div>
            <a href="{{ route('hairdressing-suppliers.create-payment', $hairdressingSupplier) }}" class="btn btn-success me-2">
                <i class="fas fa-money-bill-wave"></i> Registrar Pago
            </a>
            <a href="{{ route('hairdressing-suppliers.show', $hairdressingSupplier) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Proveedor
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Información del Proveedor -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-dark">{{ $hairdressingSupplier->full_name }}</h5>
        </div>
        <div class="card-body py-2">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">CUIT:</small>
                    <span class="fw-bold">{{ $hairdressingSupplier->cuit ?? 'No registrado' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Teléfono:</small>
                    <span class="fw-bold">{{ $hairdressingSupplier->phone ?? 'No registrado' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Dirección:</small>
                    <span class="fw-bold">{{ $hairdressingSupplier->address ?? 'No registrada' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen de Cuenta -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center py-2">
                    <small class="text-muted text-uppercase">Importes Facturas</small>
                    <h5 class="text-primary mb-0">${{ number_format($totalDebts, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center py-2">
                    <small class="text-muted text-uppercase">Total Pagos</small>
                    <h5 class="text-success mb-0">${{ number_format($totalPayments, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center py-2">
                    <small class="text-muted text-uppercase">Excedente</small>
                    <h5 class="text-info mb-0">${{ number_format($totalCredits, 2, ',', '.') }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card {{ $currentBalance > 0 ? 'border-danger' : ($currentBalance < 0 ? 'border-success' : 'border-dark') }}">
                <div class="card-body text-center py-2">
                    <small class="text-muted text-uppercase">Saldo Final</small>
                    <h5 class="{{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-dark') }} mb-0">
                        {{ $formattedBalance }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Fórmula -->
    <div class="alert {{ $currentBalance > 0 ? 'alert-danger' : ($currentBalance < 0 ? 'alert-success' : 'alert-secondary') }} py-2 mb-4">
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-2">
            <span><strong>Importes Facturas</strong> (${{ number_format($totalDebts, 2, ',', '.') }})</span>
            <span>-</span>
            <span><strong>Pagos</strong> (${{ number_format($totalPayments, 2, ',', '.') }})</span>
            <span>-</span>
            <span><strong>Excedente</strong> (${{ number_format($totalCredits, 2, ',', '.') }})</span>
            <span>=</span>
            <span class="fw-bold fs-5">
                @if($currentBalance > 0)
                    ${{ number_format($currentBalance, 2, ',', '.') }} <span class="badge bg-danger">DEUDA</span>
                @elseif($currentBalance < 0)
                    ${{ number_format(abs($currentBalance), 2, ',', '.') }} <span class="badge bg-success">EXCEDENTE A FAVOR</span>
                @else
                    $0,00 <span class="badge bg-secondary">AL DÍA</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Historial de Movimientos -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list"></i> Historial de Movimientos</h5>
            <small class="text-muted">Ordenado cronológicamente</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">Fecha Mov.</th>
                            <th>N° Factura</th>
                            <th>Descripción</th>
                            <th class="text-end">Débito</th>
                            <th class="text-end">Crédito</th>
                            <th class="text-end px-3">Saldo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currentAccounts as $account)
                            <tr>
                                <td class="px-3">{{ $account->date->format('d/m/Y') }}</td>
                                <td>
                                    @if($account->hairdressingSupplierPurchase)
                                        <span class="badge bg-primary">{{ $account->hairdressingSupplierPurchase->receipt_number }}</span>
                                    @else
                                        <span class="text-muted">{{ $account->reference ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $account->description }}</small>
                                    @if($account->observations)
                                        <br><small class="text-muted">{{ $account->observations }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($account->type === 'debt')
                                        <span class="text-danger fw-bold">${{ number_format($account->amount, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($account->type === 'payment' || $account->type === 'credit')
                                        <span class="text-success fw-bold">${{ number_format($account->amount, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="text-end px-3">
                                    @php $rb = $account->running_balance; @endphp
                                    <span class="fw-bold {{ $rb > 0 ? 'text-danger' : ($rb < 0 ? 'text-success' : 'text-dark') }}">
                                        @if($rb < 0)
                                            -${{ number_format(abs($rb), 2, ',', '.') }}
                                        @else
                                            ${{ number_format($rb, 2, ',', '.') }}
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($account->type === 'payment' && is_null($account->hairdressing_supplier_purchase_id))
                                        <button type="button"
                                                class="btn btn-danger btn-sm delete-payment-btn"
                                                title="Eliminar pago"
                                                data-account-id="{{ $account->id }}"
                                                data-account-desc="{{ $account->description }}"
                                                data-account-amount="${{ number_format($account->amount, 2, ',', '.') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-file-invoice fa-3x text-muted mb-3 d-block"></i>
                                    No hay movimientos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($currentAccounts->count() > 0)
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td class="px-3" colspan="3"><strong>TOTALES</strong></td>
                                <td class="text-end text-danger">${{ number_format($totalDebts, 2, ',', '.') }}</td>
                                <td class="text-end text-success">${{ number_format($totalPayments + $totalCredits, 2, ',', '.') }}</td>
                                <td class="text-end px-3">
                                    <span class="{{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-dark') }}">
                                        @if($currentBalance > 0)
                                            ${{ number_format($currentBalance, 2, ',', '.') }}
                                            <small class="badge bg-danger">Debe</small>
                                        @elseif($currentBalance < 0)
                                            -${{ number_format(abs($currentBalance), 2, ',', '.') }}
                                            <small class="badge bg-success">Excedente</small>
                                        @else
                                            $0,00
                                            <small class="badge bg-secondary">Al día</small>
                                        @endif
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación de Pago -->
<div id="deletePaymentModal" class="custom-modal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                Confirmar Eliminación
            </h5>
            <button type="button" class="custom-modal-close" id="closeDeletePaymentModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="custom-modal-body">
            <p class="mb-3">¿Estás seguro de que querés eliminar este pago?</p>
            <div class="alert alert-warning">
                <strong>Descripción:</strong> <span id="modalPaymentDesc"></span><br>
                <strong>Importe:</strong> <span id="modalPaymentAmount"></span>
            </div>
            <p class="text-danger mb-0">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Esta acción no se puede deshacer.</strong>
            </p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelDeletePayment">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <form id="deletePaymentForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Sí, Eliminar
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('deletePaymentModal');
    const deleteButtons = document.querySelectorAll('.delete-payment-btn');
    const closeModal = document.getElementById('closeDeletePaymentModal');
    const cancelButton = document.getElementById('cancelDeletePayment');
    const deleteForm = document.getElementById('deletePaymentForm');
    const descSpan = document.getElementById('modalPaymentDesc');
    const amountSpan = document.getElementById('modalPaymentAmount');

    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const accountId = this.getAttribute('data-account-id');
            descSpan.textContent = this.getAttribute('data-account-desc');
            amountSpan.textContent = this.getAttribute('data-account-amount');
            deleteForm.action = '/hairdressing-suppliers/{{ $hairdressingSupplier->id }}/destroy-payment/' + accountId;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    function closeModalFn() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    closeModal.addEventListener('click', closeModalFn);
    cancelButton.addEventListener('click', closeModalFn);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModalFn();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModalFn();
    });
});
</script>
@endsection
