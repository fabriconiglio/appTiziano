@extends('layouts.app')

@section('title', 'Nuevo Cliente No Frecuente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus"></i> Nuevo Cliente No Frecuente
                    </h5>
                    <a href="{{ route('cliente-no-frecuentes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('cliente-no-frecuentes.store') }}" method="POST">
                        @csrf

                        <!-- Información del Cliente -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user"></i> Información del Cliente
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del Cliente</label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" name="nombre" 
                                       value="{{ old('nombre') }}"
                                       placeholder="Opcional - Dejar vacío si no se conoce">
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                       id="telefono" name="telefono" 
                                       value="{{ old('telefono') }}"
                                       placeholder="Opcional - Número de contacto">
                                @error('telefono')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información del Servicio -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-cut"></i> Información del Servicio
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="fecha" class="form-label">Fecha del Servicio *</label>
                                <input type="date" class="form-control @error('fecha') is-invalid @enderror" 
                                       id="fecha" name="fecha" 
                                       value="{{ old('fecha', date('Y-m-d')) }}" 
                                       max="{{ date('Y-m-d') }}" required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="peluquero" class="form-label">Peluquero *</label>
                                <input type="text" class="form-control @error('peluquero') is-invalid @enderror" 
                                       id="peluquero" name="peluquero" 
                                       value="{{ old('peluquero') }}" required
                                       placeholder="Nombre del peluquero que atendió">
                                @error('peluquero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="monto" class="form-label">Valor del Servicio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control @error('monto') is-invalid @enderror" 
                                           id="monto" name="monto" 
                                           value="{{ old('monto') }}" required
                                           placeholder="0.00">
                                </div>
                                @error('monto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="servicios" class="form-label">Servicios Realizados</label>
                                <input type="text" class="form-control @error('servicios') is-invalid @enderror" 
                                       id="servicios" name="servicios" 
                                       value="{{ old('servicios') }}"
                                       placeholder="Ej: Corte + Barba, Tinte, etc.">
                                @error('servicios')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-sticky-note"></i> Observaciones
                                </h6>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                                <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                          id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Cualquier observación adicional sobre el servicio o cliente...">{{ old('observaciones') }}</textarea>
                                @error('observaciones')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('cliente-no-frecuentes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cliente
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
    // Establecer fecha actual por defecto
    const fechaInput = document.getElementById('fecha');
    if (!fechaInput.value) {
        fechaInput.value = new Date().toISOString().split('T')[0];
    }
    
    // Validación en tiempo real
    const montoInput = document.getElementById('monto');
    montoInput.addEventListener('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
    });
});
</script>
@endsection
