@extends('layouts.app')

@section('title', 'Editar Movimiento - ' . $distributorClient->full_name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Editar Movimiento - {{ $distributorClient->full_name }}</h5>
                    <a href="{{ route('distributor-clients.current-accounts.show', $distributorClient) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('distributor-clients.current-accounts.update', [$distributorClient, $currentAccount]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Tipo de Movimiento -->
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    Tipo de Movimiento <span class="text-danger">*</span>
                                </label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="debt" {{ old('type', $currentAccount->type) === 'debt' ? 'selected' : '' }}>Deuda</option>
                                    <option value="payment" {{ old('type', $currentAccount->type) === 'payment' ? 'selected' : '' }}>Pago</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Monto -->
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">
                                    Monto <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           name="amount" 
                                           id="amount" 
                                           step="0.01" 
                                           min="0.01"
                                           value="{{ old('amount', $currentAccount->amount) }}"
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           placeholder="0.00"
                                           required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Registro Técnico (Opcional) -->
                        @if($technicalRecords->count() > 0)
                            <div class="mb-3">
                                <label for="distributor_technical_record_id" class="form-label">
                                    Registro Técnico (Opcional)
                                </label>
                                <select name="distributor_technical_record_id" id="distributor_technical_record_id" class="form-select @error('distributor_technical_record_id') is-invalid @enderror">
                                    <option value="">Sin registro técnico</option>
                                    @foreach($technicalRecords as $record)
                                        <option value="{{ $record->id }}" {{ old('distributor_technical_record_id', $currentAccount->distributor_technical_record_id) == $record->id ? 'selected' : '' }}>
                                            {{ $record->purchase_date->format('d/m/Y') }} - ${{ number_format($record->final_amount, 2, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    Selecciona un registro técnico si este movimiento está relacionado con una compra específica.
                                </div>
                                @error('distributor_technical_record_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="row">
                            <!-- Descripción -->
                            <div class="col-md-8 mb-3">
                                <label for="description" class="form-label">
                                    Descripción <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="description" 
                                       id="description" 
                                       value="{{ old('description', $currentAccount->description) }}"
                                       class="form-control @error('description') is-invalid @enderror" 
                                       placeholder="Descripción del movimiento"
                                       required>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fecha -->
                            <div class="col-md-4 mb-3">
                                <label for="date" class="form-label">
                                    Fecha <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="date" 
                                       id="date" 
                                       value="{{ old('date', $currentAccount->date->format('Y-m-d')) }}"
                                       class="form-control @error('date') is-invalid @enderror" 
                                       required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Referencia -->
                        <div class="mb-3">
                            <label for="reference" class="form-label">
                                Referencia (Opcional)
                            </label>
                            <input type="text" 
                                   name="reference" 
                                   id="reference" 
                                   value="{{ old('reference', $currentAccount->reference) }}"
                                   class="form-control @error('reference') is-invalid @enderror" 
                                   placeholder="Número de factura, recibo, etc.">
                            <div class="form-text">
                                Número de factura, recibo, transferencia, etc.
                            </div>
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-4">
                            <label for="observations" class="form-label">
                                Observaciones (Opcional)
                            </label>
                            <textarea name="observations" 
                                      id="observations" 
                                      rows="3"
                                      class="form-control @error('observations') is-invalid @enderror" 
                                      placeholder="Observaciones adicionales">{{ old('observations', $currentAccount->observations) }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Información del Movimiento -->
                        <div class="card mb-4 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Información del Movimiento</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Creado por:</small><br>
                                        <strong>{{ $currentAccount->user->name }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Fecha de creación:</small><br>
                                        <strong>{{ $currentAccount->created_at->format('d/m/Y H:i') }}</strong>
                                    </div>
                                    @if($currentAccount->updated_at != $currentAccount->created_at)
                                        <div class="col-md-12 mt-2">
                                            <small class="text-muted">Última modificación:</small><br>
                                            <strong>{{ $currentAccount->updated_at->format('d/m/Y H:i') }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('distributor-clients.current-accounts.show', $distributorClient) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Movimiento
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
    // Auto-completar monto si se selecciona un registro técnico
    const technicalRecordSelect = document.getElementById('distributor_technical_record_id');
    const amountInput = document.getElementById('amount');
    const descriptionInput = document.getElementById('description');

    technicalRecordSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const text = selectedOption.text;
            const amountMatch = text.match(/\$([\d,]+\.?\d*)/);
            if (amountMatch) {
                const amount = amountMatch[1].replace(',', '');
                amountInput.value = amount;
            }
            
            // Auto-completar descripción solo si está vacía
            if (!descriptionInput.value) {
                descriptionInput.value = 'Deuda por compra - ' + new Date().toLocaleDateString('es-ES');
            }
        }
    });
});
</script>
@endsection 