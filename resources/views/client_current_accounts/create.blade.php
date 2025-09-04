@extends('layouts.app')

@section('title', 'Nuevo Movimiento - ' . $client->full_name)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Nuevo Movimiento - {{ $client->full_name }}</h5>
                    <a href="{{ route('client-current-accounts.index') }}" class="btn btn-secondary btn-sm">
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

                    <!-- Información del cliente -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-user me-2"></i>
                            Información del Cliente
                        </h6>
                        <p class="mb-1"><strong>Nombre:</strong> {{ $client->full_name }}</p>
                        <p class="mb-1"><strong>DNI:</strong> {{ $client->dni }}</p>
                        <p class="mb-0"><strong>Saldo actual:</strong> 
                            <span class="badge {{ $client->current_balance > 0 ? 'bg-danger' : ($client->current_balance < 0 ? 'bg-success' : 'bg-secondary') }}">
                                {{ $client->formatted_balance }}
                            </span>
                        </p>
                    </div>

                    <form action="{{ route('clients.current-accounts.store', $client) }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="type" class="form-label">Tipo de Movimiento <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="debt" {{ old('type') == 'debt' ? 'selected' : '' }}>Deuda</option>
                                    <option value="payment" {{ old('type') == 'payment' ? 'selected' : '' }}>Pago</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="amount" class="form-label">Monto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" 
                                           name="amount" 
                                           step="0.01" 
                                           min="0.01" 
                                           value="{{ old('amount') }}" 
                                           required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('description') is-invalid @enderror" 
                                   id="description" 
                                   name="description" 
                                   value="{{ old('description') }}" 
                                   placeholder="Ej: Corte de cabello, Tratamiento capilar, Pago parcial, etc."
                                   required>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('date') is-invalid @enderror" 
                                       id="date" 
                                       name="date" 
                                       value="{{ old('date', now()->format('Y-m-d')) }}" 
                                       required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="reference" class="form-label">Referencia</label>
                                <input type="text" 
                                       class="form-control @error('reference') is-invalid @enderror" 
                                       id="reference" 
                                       name="reference" 
                                       value="{{ old('reference') }}" 
                                       placeholder="Ej: Factura #123, Recibo #456, etc.">
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observations" class="form-label">Observaciones</label>
                            <textarea class="form-control @error('observations') is-invalid @enderror" 
                                      id="observations" 
                                      name="observations" 
                                      rows="3" 
                                      placeholder="Observaciones adicionales sobre el movimiento...">{{ old('observations') }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('client-current-accounts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 