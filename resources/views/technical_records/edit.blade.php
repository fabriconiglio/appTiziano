<!-- resources/views/technical_records/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Editar Ficha Técnica')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Editar Ficha Técnica - {{ $client->name }} {{ $client->surname }}</h5>
                        <a href="{{ route('clients.show', $client) }}" class="btn btn-secondary btn-sm">
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

                        <form action="{{ route('clients.technical-records.update', [$client, $technicalRecord]) }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="service_date" class="form-label">Fecha del Servicio <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('service_date') is-invalid @enderror"
                                           id="service_date" name="service_date"
                                           value="{{ old('service_date', $technicalRecord->service_date->format('Y-m-d\TH:i')) }}" required>
                                    @error('service_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="service_cost" class="form-label">Valor del Servicio <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" class="form-control @error('service_cost') is-invalid @enderror"
                                               id="service_cost" name="service_cost"
                                               value="{{ old('service_cost', $technicalRecord->service_cost ?? 0.00) }}" required>
                                    </div>
                                    @error('service_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>


                            <div class="mb-3">
                                <label for="hair_treatments" class="form-label">Tratamientos Realizados</label>
                                <textarea class="form-control @error('hair_treatments') is-invalid @enderror"
                                          id="hair_treatments" name="hair_treatments"
                                          rows="3">{{ old('hair_treatments', $technicalRecord->hair_treatments) }}</textarea>
                                @error('hair_treatments')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="products_used" class="form-label">Productos Utilizados</label>
                                <select id="products_used"
                                        name="products_used[]"
                                        multiple="multiple">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                            {{ in_array($product->id, old('products_used', $technicalRecord->products_used ?? [])) ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- MOD-030 (main): Sección de Información de Cuenta Corriente -->
                            <div class="card mb-3 border-info">
                                <div class="card-header bg-info">
                                    <h6 class="mb-0 text-dark">
                                        <i class="fas fa-calculator me-2"></i>
                                        Información de Cuenta Corriente
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Saldo Actual</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="text" class="form-control" id="current_balance" 
                                                       value="{{ number_format($currentBalanceWithoutThisRecord, 2, ',', '.') }}" 
                                                       readonly style="background-color: #e9ecef;">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Estado</label>
                                            <div class="mt-2">
                                                @if($currentBalanceWithoutThisRecord > 0)
                                                    <span class="badge bg-danger fs-6">Con Deuda</span>
                                                @elseif($currentBalanceWithoutThisRecord < 0)
                                                    <span class="badge bg-success fs-6">A Favor</span>
                                                @else
                                                    <span class="badge bg-secondary fs-6">Al Día</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Ajuste Automático</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="text" class="form-control" id="balance_adjustment" 
                                                       value="0,00" readonly style="background-color: #e9ecef;">
                                            </div>
                                            <small class="form-text text-muted">
                                                @if($currentBalanceWithoutThisRecord > 0)
                                                    El servicio se sumará a la deuda existente
                                                @elseif($currentBalanceWithoutThisRecord < 0)
                                                    El crédito se aplicará a este servicio
                                                @else
                                                    Sin ajuste necesario
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    
                                    <!-- Opción para decidir si registrar en cuenta corriente -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="use_current_account" name="use_current_account" value="1" checked>
                                                <label class="form-check-label" for="use_current_account">
                                                    <strong>Registrar en cuenta corriente</strong>
                                                </label>
                                                <small class="form-text text-muted d-block">
                                                    Marca esta opción si quieres que este servicio se registre en la cuenta corriente del cliente. 
                                                    Si la desmarcas, el servicio se registrará como pagado completamente sin afectar la cuenta corriente.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Explicación del cálculo -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <h6 class="alert-heading">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Cálculo del Monto Final (Editando Ficha Técnica)
                                                </h6>
                                                @if($currentBalanceWithoutThisRecord > 0)
                                                    <p class="mb-1">
                                                        <strong>Deuda actual (sin esta ficha):</strong> ${{ number_format($currentBalanceWithoutThisRecord, 2, ',', '.') }}<br>
                                                        <strong>+ Servicio actual:</strong> $<span id="service_amount_display">0,00</span><br>
                                                        <strong>= Total a pagar:</strong> $<span id="total_debt_display">0,00</span>
                                                    </p>
                                                @elseif($currentBalanceWithoutThisRecord < 0)
                                                    <p class="mb-1">
                                                        <strong>Crédito disponible (sin esta ficha):</strong> ${{ number_format(abs($currentBalanceWithoutThisRecord), 2, ',', '.') }}<br>
                                                        <strong>- Servicio actual:</strong> $<span id="service_amount_display">0,00</span><br>
                                                        <strong>= Total a pagar:</strong> $<span id="total_debt_display">0,00</span>
                                                    </p>
                                                @else
                                                    <p class="mb-1">
                                                        <strong>Servicio actual:</strong> $<span id="service_amount_display">0,00</span><br>
                                                        <strong>= Se registrará como deuda:</strong> $<span id="total_debt_display">0,00</span>
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Método de Pago</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror"
                                            id="payment_method" name="payment_method">
                                        <option value="">Seleccionar método</option>
                                        <option value="efectivo" {{ old('payment_method', $technicalRecord->payment_method) == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="tarjeta" {{ old('payment_method', $technicalRecord->payment_method) == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                        <option value="transferencia" {{ old('payment_method', $technicalRecord->payment_method) == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                        <option value="deuda" {{ old('payment_method', $technicalRecord->payment_method) == 'deuda' ? 'selected' : '' }}>Deuda/Deudor</option>
                                    </select>
                                    @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        @if($technicalRecord->photos)
                                <div id="photo-container" class="mb-3">
                                    <label class="form-label">Fotos Actuales</label>
                                    <div class="row">
                                        @foreach($technicalRecord->photos as $photo)
                                            <div class="col-md-4 mb-2 photo-item">
                                                <div class="card">
                                                    <img src="{{ Storage::url($photo) }}" class="card-img-top" alt="Foto del tratamiento">
                                                    <div class="card-body text-center">
                                                        <button type="button"
                                                                class="btn btn-danger btn-sm delete-photo"
                                                                data-photo="{{ $photo }}"
                                                                data-url="{{ route('clients.technical-records.delete-photo', [$client, $technicalRecord]) }}">
                                                            <i class="fas fa-trash"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="photos" class="form-label">Agregar Nuevas Fotos</label>
                                <input type="file" class="form-control @error('photos') is-invalid @enderror"
                                       id="photos" name="photos[]" multiple accept="image/*">
                                <div class="form-text">Puedes seleccionar múltiples fotos. Máximo 2MB por imagen.</div>
                                @error('photos')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="observations" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror"
                                          id="observations" name="observations"
                                          rows="3">{{ old('observations', $technicalRecord->observations) }}</textarea>
                                @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="next_appointment_notes" class="form-label">Notas para Próxima Cita</label>
                                <textarea class="form-control @error('next_appointment_notes') is-invalid @enderror"
                                          id="next_appointment_notes" name="next_appointment_notes"
                                          rows="2">{{ old('next_appointment_notes', $technicalRecord->next_appointment_notes) }}</textarea>
                                @error('next_appointment_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-secondary">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Actualizar Ficha Técnica
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para productos utilizados
            $('#products_used').select2({
                placeholder: 'Seleccionar productos...',
                allowClear: true,
                width: '100%',
                language: 'es'
            });

            // Inicializar Summernote para observaciones
            $('#observations').summernote({
                placeholder: 'Agrega aquí las observaciones de la ficha técnica...',
                tabsize: 2,
                height: 200,
                lang: 'es-ES',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });

            // MOD-030 (main): Lógica para cálculo automático de cuenta corriente (edición)
            const currentBalance = {{ $currentBalanceWithoutThisRecord }};
            
            function updateBalanceCalculation() {
                const serviceCost = parseFloat($('#service_cost').val()) || 0;
                let balanceAdjustment = 0;
                let finalAmount = serviceCost;
                
                if (currentBalance !== 0) {
                    balanceAdjustment = currentBalance;
                    finalAmount = Math.max(0, serviceCost + balanceAdjustment);
                }
                
                // Actualizar displays
                $('#balance_adjustment').val(balanceAdjustment.toLocaleString('es-AR', {minimumFractionDigits: 2}));
                $('#service_amount_display').text(serviceCost.toLocaleString('es-AR', {minimumFractionDigits: 2}));
                $('#total_debt_display').text(finalAmount.toLocaleString('es-AR', {minimumFractionDigits: 2}));
            }
            
            // Actualizar cálculo cuando cambie el costo del servicio
            $('#service_cost').on('input change', updateBalanceCalculation);
            
            // Inicializar cálculo
            updateBalanceCalculation();
        });
    </script>
@endpush

@endsection
