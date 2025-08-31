@extends('layouts.app')

@section('title', 'Presupuesto ' . $quotation->quotation_number . ' - ' . $distributorClient->full_name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-dark">
                        Presupuesto {{ $quotation->quotation_number }} - {{ $distributorClient->full_name }}
                    </h5>
                    <div>
                        <a href="{{ route('distributor-clients.quotations.edit', [$distributorClient, $quotation]) }}" 
                           class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('distributor-clients.quotations.export-pdf', [$distributorClient, $quotation]) }}" 
                           class="btn btn-danger btn-sm me-2" target="_blank">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </a>
                        <a href="{{ route('distributor-quotations.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
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

                    <!-- Información del Presupuesto -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Número de Presupuesto</label>
                            <p class="form-control-plaintext">{{ $quotation->quotation_number }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha del Presupuesto</label>
                            <p class="form-control-plaintext">{{ $quotation->quotation_date->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Válido Hasta</label>
                            <p class="form-control-plaintext">{{ $quotation->valid_until->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tipo de Presupuesto</label>
                            <p class="form-control-plaintext">{{ $quotation->type_formatted }}</p>
                        </div>
                    </div>

                    <!-- Configuración de Precios -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Porcentaje de IVA</label>
                            <p class="form-control-plaintext">{{ $quotation->tax_percentage }}%</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Porcentaje de Descuento</label>
                            <p class="form-control-plaintext">{{ $quotation->discount_percentage }}%</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Condiciones de Pago</label>
                            <p class="form-control-plaintext">{{ $quotation->payment_terms ?? 'No especificado' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Condiciones de Entrega</label>
                            <p class="form-control-plaintext">{{ $quotation->delivery_terms ?? 'No especificado' }}</p>
                        </div>
                    </div>

                    <!-- Productos del Presupuesto -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3">Productos del Presupuesto</h6>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($quotation->products_quoted as $product)
                                        <tr>
                                            <td>
                                                @php
                                                    $productInfo = App\Models\SupplierInventory::find($product['product_id']);
                                                @endphp
                                                {{ $productInfo ? $productInfo->product_name : 'Producto no encontrado' }}
                                                @if($productInfo && $productInfo->description)
                                                    <br><small class="text-muted">{{ $productInfo->description }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $product['quantity'] }}</td>
                                            <td>${{ number_format($product['price'], 2) }}</td>
                                            <td>${{ number_format($product['quantity'] * $product['price'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">
                                                No hay productos en este presupuesto
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Resumen del Presupuesto -->
                    <div class="summary-section mb-4">
                        <h6 class="border-bottom pb-2 mb-3">Resumen del Presupuesto</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="summary-row">
                                    <span>Subtotal:</span>
                                    <span>${{ number_format($quotation->subtotal, 2) }}</span>
                                </div>
                                <div class="summary-row">
                                    <span>IVA ({{ $quotation->tax_percentage }}%):</span>
                                    <span>${{ number_format($quotation->tax_amount, 2) }}</span>
                                </div>
                                <div class="summary-row">
                                    <span>Total con IVA:</span>
                                    <span>${{ number_format($quotation->total_amount, 2) }}</span>
                                </div>
                                <div class="summary-row">
                                    <span>Descuento ({{ $quotation->discount_percentage }}%):</span>
                                    <span>${{ number_format($quotation->discount_amount, 2) }}</span>
                                </div>
                                <div class="summary-row summary-total">
                                    <span>Total Final:</span>
                                    <span>${{ number_format($quotation->final_amount, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Estado del Presupuesto</h6>
                                        <p class="mb-2">
                                            <span class="badge {{ $quotation->status === 'active' ? 'bg-success' : ($quotation->status === 'expired' ? 'bg-warning' : 'bg-secondary') }}">
                                                {{ $quotation->status_formatted }}
                                            </span>
                                        </p>
                                        @if($quotation->isExpired())
                                            <small class="text-muted">Este presupuesto ha expirado</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones y Términos -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Observaciones</label>
                            <div class="border rounded p-3 bg-light">
                                {!! $quotation->observations ?: '<em class="text-muted">Sin observaciones</em>' !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Términos y Condiciones</label>
                            <div class="border rounded p-3 bg-light">
                                {!! $quotation->terms_conditions ?: '<em class="text-muted">Sin términos y condiciones</em>' !!}
                            </div>
                        </div>
                    </div>

                    <!-- Fotos del Presupuesto -->
                    @if(!empty($quotation->photos))
                        <div class="mb-4">
                            <label class="form-label fw-bold">Fotos del Presupuesto</label>
                            <div class="row">
                                @foreach($quotation->photos as $photo)
                                    <div class="col-md-3 mb-3">
                                        <img src="{{ Storage::url($photo) }}" 
                                             alt="Foto del presupuesto" 
                                             class="img-fluid rounded border"
                                             style="max-height: 200px; width: 100%; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Acciones -->
                    <div class="d-flex justify-content-center gap-2 mt-4">
                        @if($quotation->isActive())
                            <button type="button" class="btn btn-warning" 
                                    data-bs-toggle="modal" data-bs-target="#cancelQuotationModal">
                                <i class="fas fa-ban"></i> Cancelar Presupuesto
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cancelación -->
<div class="modal fade" id="cancelQuotationModal" tabindex="-1" aria-labelledby="cancelQuotationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelQuotationModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirmar Cancelación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    ¿Estás seguro de que quieres <strong>cancelar</strong> este presupuesto?
                </p>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Presupuesto:</strong> {{ $quotation->quotation_number }}<br>
                    <strong>Cliente:</strong> {{ $distributorClient->full_name }}<br>
                    <strong>Monto:</strong> ${{ number_format($quotation->final_amount, 2) }}
                </div>
                <p class="text-muted small">
                    Esta acción cambiará el estado del presupuesto a "Cancelado" y no se podrá revertir.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>No, mantener activo
                </button>
                <form action="{{ route('distributor-clients.quotations.change-status', [$distributorClient, $quotation]) }}" 
                      method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-ban me-2"></i>Sí, cancelar presupuesto
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .summary-section {
        background-color: #e9ecef;
        padding: 20px;
        border-radius: 8px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 16px;
    }
    .summary-total {
        font-size: 20px;
        font-weight: bold;
        border-top: 2px solid #dee2e6;
        padding-top: 10px;
        margin-top: 10px;
    }
</style>
@endsection 