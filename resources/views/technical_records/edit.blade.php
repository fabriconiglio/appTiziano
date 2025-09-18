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

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="hair_type" class="form-label">Tipo de Cabello</label>
                                    <select class="form-select @error('hair_type') is-invalid @enderror"
                                            id="hair_type" name="hair_type">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="liso" {{ old('hair_type', $technicalRecord->hair_type) == 'liso' ? 'selected' : '' }}>Liso</option>
                                        <option value="ondulado" {{ old('hair_type', $technicalRecord->hair_type) == 'ondulado' ? 'selected' : '' }}>Ondulado</option>
                                        <option value="rizado" {{ old('hair_type', $technicalRecord->hair_type) == 'rizado' ? 'selected' : '' }}>Rizado</option>
                                        <option value="crespo" {{ old('hair_type', $technicalRecord->hair_type) == 'crespo' ? 'selected' : '' }}>Crespo</option>
                                    </select>
                                    @error('hair_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="scalp_condition" class="form-label">Condición del Cuero Cabelludo</label>
                                    <select class="form-select @error('scalp_condition') is-invalid @enderror"
                                            id="scalp_condition" name="scalp_condition">
                                        <option value="">Seleccionar condición</option>
                                        <option value="normal" {{ old('scalp_condition', $technicalRecord->scalp_condition) == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="seco" {{ old('scalp_condition', $technicalRecord->scalp_condition) == 'seco' ? 'selected' : '' }}>Seco</option>
                                        <option value="graso" {{ old('scalp_condition', $technicalRecord->scalp_condition) == 'graso' ? 'selected' : '' }}>Graso</option>
                                        <option value="sensible" {{ old('scalp_condition', $technicalRecord->scalp_condition) == 'sensible' ? 'selected' : '' }}>Sensible</option>
                                    </select>
                                    @error('scalp_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="current_hair_color" class="form-label">Color Actual</label>
                                    <input type="text" class="form-control @error('current_hair_color') is-invalid @enderror"
                                           id="current_hair_color" name="current_hair_color"
                                           value="{{ old('current_hair_color', $technicalRecord->current_hair_color) }}">
                                    @error('current_hair_color')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="desired_hair_color" class="form-label">Color Deseado</label>
                                <input type="text" class="form-control @error('desired_hair_color') is-invalid @enderror"
                                       id="desired_hair_color" name="desired_hair_color"
                                       value="{{ old('desired_hair_color', $technicalRecord->desired_hair_color) }}">
                                @error('desired_hair_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
        });
    </script>
@endpush

@endsection
