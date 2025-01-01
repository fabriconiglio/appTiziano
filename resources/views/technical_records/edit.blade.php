<!-- resources/views/technical_records/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Editar Ficha Técnica')

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
                                <label class="form-label">Productos Utilizados</label>
                                <div id="products-container">
                                    @foreach($technicalRecord->products_used ?? [] as $product)
                                        <div class="input-group mb-2">
                                            <input type="text" name="products_used[]"
                                                   class="form-control" value="{{ $product }}">
                                            <button type="button" class="btn btn-danger remove-product">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                    <div class="input-group mb-2">
                                        <input type="text" name="products_used[]"
                                               class="form-control" placeholder="Nuevo producto">
                                        <button type="button" class="btn btn-success add-product">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @if($technicalRecord->photos)
                                <div class="mb-3">
                                    <label class="form-label">Fotos Actuales</label>
                                    <div class="row">
                                        @foreach($technicalRecord->photos as $photo)
                                            <div class="col-md-4 mb-2">
                                                <div class="card">
                                                    <img src="{{ Storage::url($photo) }}" class="card-img-top" alt="Foto del tratamiento">
                                                    <div class="card-body text-center">
                                                        <button type="button" class="btn btn-danger btn-sm delete-photo"
                                                                data-photo="{{ $photo }}">
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Manejar la adición de campos de productos
                const container = document.getElementById('products-container');

                document.querySelectorAll('.add-product').forEach(button => {
                    button.addEventListener('click', function() {
                        const newRow = document.createElement('div');
                        newRow.className = 'input-group mb-2';
                        newRow.innerHTML = `
                    <input type="text" name="products_used[]" class="form-control" placeholder="Nombre del producto">
                    <button type="button" class="btn btn-danger remove-product">
                        <i class="fas fa-minus"></i>
                    </button>
                `;
                        container.insertBefore(newRow, this.parentElement.parentElement);

                        // Agregar evento para remover el campo
                        newRow.querySelector('.remove-product').addEventListener('click', function() {
                            newRow.remove();
                        });
                    });
                });

                // Manejar la eliminación de campos de productos existentes
                document.querySelectorAll('.remove-product').forEach(button => {
                    button.addEventListener('click', function() {
                        this.parentElement.remove();
                    });
                });

                // Manejar la eliminación de fotos
                document.querySelectorAll('.delete-photo').forEach(button => {
                    button.addEventListener('click', function() {
                        if (confirm('¿Estás seguro de que quieres eliminar esta foto?')) {
                            const photoPath = this.dataset.photo;
                            // Aquí podrías agregar lógica para eliminar la foto
                            this.closest('.col-md-4').remove();
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
