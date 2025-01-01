<!-- resources/views/clients/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Editar Cliente: {{ $client->name }} {{ $client->surname }}</h5>
                        <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-sm">
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

                        <form action="{{ route('clients.update', $client) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $client->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="surname" class="form-label">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                           id="surname" name="surname" value="{{ old('surname', $client->surname) }}" required>
                                    @error('surname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $client->email) }}">
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone', $client->phone) }}">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="birth_date" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control @error('birth_date') is-invalid @enderror"
                                       id="birth_date" name="birth_date"
                                       value="{{ old('birth_date', $client->birth_date ? $client->birth_date->format('Y-m-d') : '') }}">
                                @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="allergies" class="form-label">Alergias</label>
                                <textarea class="form-control @error('allergies') is-invalid @enderror"
                                          id="allergies" name="allergies" rows="2">{{ old('allergies', $client->allergies) }}</textarea>
                                @error('allergies')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Indique si el cliente tiene alguna alergia o sensibilidad conocida.</div>
                            </div>

                            <div class="mb-3">
                                <label for="observations" class="form-label">Observaciones</label>
                                <textarea class="form-control @error('observations') is-invalid @enderror"
                                          id="observations" name="observations" rows="3">{{ old('observations', $client->observations) }}</textarea>
                                @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <div>
                                    <!-- Botón de eliminar con confirmación -->
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Eliminar Cliente
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        Actualizar Cliente
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar este cliente? Esta acción no se puede deshacer.</p>
                    <p class="text-danger"><strong>Nota:</strong> Se eliminarán también todas las fichas técnicas asociadas.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form action="{{ route('clients.destroy', $client) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection