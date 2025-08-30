@extends('layouts.app')

@section('title', 'Nuevo Proveedor de Peluquería')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Nuevo Proveedor de Peluquería</h4>
                    <a href="{{ route('hairdressing-suppliers.index') }}" class="btn btn-secondary">
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

                    <form action="{{ route('hairdressing-suppliers.store') }}" method="POST">
                        @csrf

                        <!-- Información Básica -->
                        <div class="row mb-4">
                            <h5>Información Básica</h5>
                            <hr>
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre del Proveedor *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="business_name" class="form-label">Razón Social</label>
                                <input type="text" class="form-control @error('business_name') is-invalid @enderror" 
                                       id="business_name" name="business_name" value="{{ old('business_name') }}">
                                @error('business_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cuit" class="form-label">CUIT</label>
                                <input type="text" class="form-control @error('cuit') is-invalid @enderror" 
                                       id="cuit" name="cuit" value="{{ old('cuit') }}">
                                @error('cuit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tax_category" class="form-label">Categoría Fiscal</label>
                                <select class="form-select @error('tax_category') is-invalid @enderror" 
                                        id="tax_category" name="tax_category">
                                    <option value="">Seleccionar categoría</option>
                                    <option value="Responsable Inscripto" {{ old('tax_category') == 'Responsable Inscripto' ? 'selected' : '' }}>Responsable Inscripto</option>
                                    <option value="Monotributista" {{ old('tax_category') == 'Monotributista' ? 'selected' : '' }}>Monotributista</option>
                                    <option value="Exento" {{ old('tax_category') == 'Exento' ? 'selected' : '' }}>Exento</option>
                                    <option value="Consumidor Final" {{ old('tax_category') == 'Consumidor Final' ? 'selected' : '' }}>Consumidor Final</option>
                                </select>
                                @error('tax_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="row mb-4">
                            <h5>Información de Contacto</h5>
                            <hr>
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                       id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                                @error('contact_person')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Teléfono</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Sitio Web</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                       id="website" name="website" value="{{ old('website') }}">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="address" class="form-label">Dirección</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información Adicional -->
                        <div class="row mb-4">
                            <h5>Información Adicional</h5>
                            <hr>
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Notas</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Proveedor Activo
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('hairdressing-suppliers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Proveedor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 