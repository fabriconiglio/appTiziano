@extends('layouts.app')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Detalle del Proveedor')

<style>
.stats-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: none;
    border-radius: 12px;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.stats-card .card-body {
    padding: 1.25rem 0.75rem;
}

.stats-card h3 {
    font-size: 0.9rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stats-card small {
    font-size: 0.7rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
}

.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}
</style>

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <!-- Información del Proveedor -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> {{ $supplier->name }}
                    </h5>
                    <div>
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle"></i> Información Básica
                            </h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nombre:</strong></td>
                                    <td>{{ $supplier->name }}</td>
                                </tr>
                                @if($supplier->business_name)
                                <tr>
                                    <td><strong>Razón Social:</strong></td>
                                    <td>{{ $supplier->business_name }}</td>
                                </tr>
                                @endif
                                @if($supplier->cuit)
                                <tr>
                                    <td><strong>CUIT:</strong></td>
                                    <td>{{ $supplier->cuit }}</td>
                                </tr>
                                @endif
                                @if($supplier->tax_category)
                                <tr>
                                    <td><strong>Categoría Impositiva:</strong></td>
                                    <td>{{ $supplier->tax_category }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Estado:</strong></td>
                                    <td>
                                        <span class="badge {{ $supplier->status_badge_class }}">
                                            {{ $supplier->status_text }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-address-book"></i> Información de Contacto
                            </h6>
                            <table class="table table-borderless">
                                @if($supplier->contact_person)
                                <tr>
                                    <td><strong>Contacto:</strong></td>
                                    <td>{{ $supplier->contact_person }}</td>
                                </tr>
                                @endif
                                @if($supplier->email)
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($supplier->phone)
                                <tr>
                                    <td><strong>Teléfono:</strong></td>
                                    <td>
                                        <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($supplier->website)
                                <tr>
                                    <td><strong>Sitio Web:</strong></td>
                                    <td>
                                        <a href="{{ $supplier->website }}" target="_blank">{{ $supplier->website }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($supplier->address)
                                <tr>
                                    <td><strong>Dirección:</strong></td>
                                    <td>{{ $supplier->address }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>

                        <!-- Información Adicional -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-sticky-note"></i> Información Adicional
                            </h6>
                            @if($supplier->notes)
                                <p>{{ $supplier->notes }}</p>
                            @else
                                <p class="text-muted">No hay notas adicionales.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compras al Proveedor -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-shopping-cart"></i> Compras al Proveedor
                    </h6>
                </div>
                <div class="card-body">
                    @if($supplier->supplierPurchases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>N° Boleta</th>
                                        <th>Total</th>
                                        <th>Pago</th>
                                        <th>Saldo</th>
                                        <th>Boleta</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->supplierPurchases->sortByDesc('purchase_date') as $purchase)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $purchase->purchase_date->format('d/m/Y') }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $purchase->receipt_number }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-success">${{ number_format($purchase->total_amount, 2, ',', '.') }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary">${{ number_format($purchase->payment_amount, 2, ',', '.') }}</div>
                                            </td>
                                            <td>
                                                @if($purchase->balance_amount > 0)
                                                    <span class="badge bg-danger text-white">${{ number_format($purchase->balance_amount, 2, ',', '.') }}</span>
                                                @else
                                                    <span class="badge bg-success">Pagado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($purchase->receipt_file)
                                                    <a href="{{ Storage::url($purchase->receipt_file) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i> Ver
                                                    </a>
                                                @else
                                                    <span class="text-muted">Sin archivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($purchase->notes)
                                                    <small class="text-muted">{{ Str::limit($purchase->notes, 50) }}</small>
                                                @else
                                                    <span class="text-muted">Sin observaciones</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('suppliers.edit-purchase', ['supplier' => $supplier, 'purchase' => $purchase]) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Editar compra">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm delete-purchase-btn" 
                                                            title="Eliminar compra"
                                                            data-purchase-id="{{ $purchase->id }}"
                                                            data-purchase-number="{{ $purchase->receipt_number }}"
                                                            data-supplier-name="{{ $supplier->name }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No hay compras registradas para este proveedor</h6>
                            <p class="text-muted">Comienza registrando tu primera compra usando el botón "Agregar Compra"</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar con Estadísticas -->
        <div class="col-md-4">
            <!-- Estadísticas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card bg-info text-white h-100 stats-card">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 class="mb-2 fw-bold">{{ $supplier->supplierPurchases->count() }}</h3>
                                    <small class="text-white-50">Total de Compras</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-dark h-100 stats-card">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 class="mb-2 fw-bold">${{ number_format($supplier->total_debt, 2, ',', '.') }}</h3>
                                    <small class="text-dark-50">Le Debo</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white h-100 stats-card">
                                <div class="card-body text-center d-flex flex-column justify-content-center">
                                    <h3 class="mb-2 fw-bold">${{ number_format($supplier->total_paid, 2, ',', '.') }}</h3>
                                    <small class="text-white-50">Pagué</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('suppliers.create-purchase', $supplier) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Agregar Compra
                        </a>
                        <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-{{ $supplier->is_active ? 'warning' : 'success' }} btn-sm w-100">
                                <i class="fas fa-{{ $supplier->is_active ? 'pause' : 'play' }}"></i>
                                {{ $supplier->is_active ? 'Desactivar' : 'Activar' }} Proveedor
                            </button>
                        </form>
                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" 
                              onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-trash"></i> Eliminar Proveedor
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="deletePurchaseModal" class="custom-modal">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                Confirmar Eliminación
            </h5>
            <button type="button" class="custom-modal-close" id="closeDeleteModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="custom-modal-body">
            <p class="mb-3">¿Estás seguro de que quieres eliminar esta compra?</p>
            <div class="alert alert-warning">
                <strong>Compra:</strong> <span id="modalPurchaseNumber"></span><br>
                <strong>Proveedor:</strong> <span id="modalSupplierName"></span>
            </div>
            <p class="text-danger mb-0">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Esta acción no se puede deshacer.</strong>
            </p>
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelDelete">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <form id="deletePurchaseForm" method="POST" style="display: inline;">
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
    // Funcionalidad del modal de eliminación
    const modal = document.getElementById('deletePurchaseModal');
    const deleteButtons = document.querySelectorAll('.delete-purchase-btn');
    const closeModal = document.getElementById('closeDeleteModal');
    const cancelButton = document.getElementById('cancelDelete');
    const deleteForm = document.getElementById('deletePurchaseForm');
    const purchaseNumberSpan = document.getElementById('modalPurchaseNumber');
    const supplierNameSpan = document.getElementById('modalSupplierName');

    // Abrir modal
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const purchaseId = this.getAttribute('data-purchase-id');
            const purchaseNumber = this.getAttribute('data-purchase-number');
            const supplierName = this.getAttribute('data-supplier-name');
            
            purchaseNumberSpan.textContent = purchaseNumber;
            supplierNameSpan.textContent = supplierName;
            deleteForm.action = `/suppliers/{{ $supplier->id }}/destroy-purchase/${purchaseId}`;
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    // Cerrar modal
    function closeModalFunction() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    closeModal.addEventListener('click', closeModalFunction);
    cancelButton.addEventListener('click', closeModalFunction);

    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModalFunction();
        }
    });

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModalFunction();
        }
    });
});
</script>
@endsection 