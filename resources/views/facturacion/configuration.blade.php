@extends('layouts.app')

@section('title', 'Configuración AFIP')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Configuración AFIP
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('facturacion.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="button" class="btn btn-info btn-sm" id="validate-config">
                            <i class="fas fa-check"></i> Validar Configuración
                        </button>
                    </div>
                </div>

                <form action="{{ route('facturacion.configuration.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <!-- Configuración básica -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-building"></i> Información de la Empresa</h5>
                                
                                <div class="form-group">
                                    <label for="afip_cuit">CUIT *</label>
                                    <input type="text" name="afip_cuit" id="afip_cuit" 
                                           class="form-control @error('afip_cuit') is-invalid @enderror" 
                                           value="{{ old('afip_cuit', $configurations->where('key', 'afip_cuit')->first()->decrypted_value ?? '') }}" 
                                           placeholder="20123456789" maxlength="11" required>
                                    <small class="form-text text-muted">CUIT de la empresa (11 dígitos)</small>
                                    @error('afip_cuit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="afip_point_of_sale">Punto de Venta *</label>
                                    <input type="text" name="afip_point_of_sale" id="afip_point_of_sale" 
                                           class="form-control @error('afip_point_of_sale') is-invalid @enderror" 
                                           value="{{ old('afip_point_of_sale', $configurations->where('key', 'afip_point_of_sale')->first()->value ?? '1') }}" 
                                           placeholder="1" required>
                                    <small class="form-text text-muted">Número del punto de venta en AFIP</small>
                                    @error('afip_point_of_sale')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="afip_tax_rate">Tasa de IVA (%) *</label>
                                    <input type="number" name="afip_tax_rate" id="afip_tax_rate" 
                                           class="form-control @error('afip_tax_rate') is-invalid @enderror" 
                                           value="{{ old('afip_tax_rate', $configurations->where('key', 'afip_tax_rate')->first()->value ?? '21.00') }}" 
                                           step="0.01" min="0" max="100" required>
                                    <small class="form-text text-muted">Tasa de IVA por defecto</small>
                                    @error('afip_tax_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5><i class="fas fa-server"></i> Configuración del Servidor</h5>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" name="afip_production" id="afip_production" 
                                               class="form-check-input" 
                                               {{ old('afip_production', $configurations->where('key', 'afip_production')->first()->value ?? 'false') == 'true' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="afip_production">
                                            Modo Producción
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <strong>Desactivado:</strong> Modo testing/sandbox<br>
                                        <strong>Activado:</strong> Modo producción (facturas reales)
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="afip_certificate_path">Ruta del Certificado *</label>
                                    <input type="text" name="afip_certificate_path" id="afip_certificate_path" 
                                           class="form-control @error('afip_certificate_path') is-invalid @enderror" 
                                           value="{{ old('afip_certificate_path', $configurations->where('key', 'afip_certificate_path')->first()->decrypted_value ?? '') }}" 
                                           placeholder="/path/to/certificate.crt" required>
                                    <small class="form-text text-muted">Ruta completa al archivo del certificado (.crt)</small>
                                    @error('afip_certificate_path')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="afip_private_key_path">Ruta de la Clave Privada *</label>
                                    <input type="text" name="afip_private_key_path" id="afip_private_key_path" 
                                           class="form-control @error('afip_private_key_path') is-invalid @enderror" 
                                           value="{{ old('afip_private_key_path', $configurations->where('key', 'afip_private_key_path')->first()->decrypted_value ?? '') }}" 
                                           placeholder="/path/to/private.key" required>
                                    <small class="form-text text-muted">Ruta completa al archivo de la clave privada (.key)</small>
                                    @error('afip_private_key_path')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="afip_access_token">Access Token AFIP SDK (Opcional)</label>
                                    <input type="text" name="afip_access_token" id="afip_access_token" 
                                           class="form-control @error('afip_access_token') is-invalid @enderror" 
                                           value="{{ old('afip_access_token', $configurations->where('key', 'afip_access_token')->first()->decrypted_value ?? '') }}" 
                                           placeholder="Token de acceso de AFIP SDK">
                                    <small class="form-text text-muted">
                                        Token opcional para usar los servicios de AFIP SDK. 
                                        Puedes obtenerlo desde <a href="https://app.afipsdk.com/" target="_blank">https://app.afipsdk.com/</a>
                                    </small>
                                    @error('afip_access_token')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Importante</h6>
                                    <ul class="mb-0">
                                        <li>Los certificados deben estar en formato PEM y ser válidos</li>
                                        <li>En modo producción, las facturas serán reales y tendrán validez fiscal</li>
                                        <li>Verifique que los archivos de certificado existan y tengan permisos de lectura</li>
                                        <li>El CUIT debe coincidir con el certificado utilizado</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Validación de configuración -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fas fa-check-circle"></i> Validación</h5>
                                <div id="validation-result"></div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Configuración
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
@endsection

@push('scripts')
<script>
// Validar configuración
document.getElementById('validate-config').addEventListener('click', function() {
    const resultDiv = document.getElementById('validation-result');
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Validando configuración...</div>';
    
    fetch('{{ route("facturacion.configuration.validate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> ' + data.message + '</div>';
        } else {
            let errorsHtml = '<div class="alert alert-danger"><i class="fas fa-times"></i> ' + data.message + '<ul>';
            if (data.errors) {
                data.errors.forEach(error => {
                    errorsHtml += '<li>' + error + '</li>';
                });
            }
            errorsHtml += '</ul></div>';
            resultDiv.innerHTML = errorsHtml;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times"></i> Error al validar configuración: ' + error.message + '</div>';
    });
});

// Formatear CUIT
document.getElementById('afip_cuit').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length > 11) {
        value = value.substring(0, 11);
    }
    this.value = value;
});

// Validar CUIT
document.getElementById('afip_cuit').addEventListener('blur', function() {
    const cuit = this.value;
    if (cuit.length === 11) {
        // Validar formato básico de CUIT
        const pattern = /^[0-9]{11}$/;
        if (!pattern.test(cuit)) {
            this.classList.add('is-invalid');
            if (!this.nextElementSibling.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'El CUIT debe contener solo números';
                this.parentNode.appendChild(feedback);
            }
        } else {
            this.classList.remove('is-invalid');
            const feedback = this.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        }
    }
});
</script>
@endpush
