<!-- resources/views/servicios/index.blade.php -->
@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Servicios</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('agenda.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Agenda
                </a>
                <a href="{{ route('servicios.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Servicio
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Duración</th>
                            <th>Precio base</th>
                            <th>Color</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servicios as $servicio)
                            <tr>
                                <td>{{ $servicio->nombre }}</td>
                                <td>{{ $servicio->duracion_minutos }} min</td>
                                <td>${{ number_format($servicio->precio_base, 2, ',', '.') }}</td>
                                <td><span class="badge" style="background: {{ $servicio->color_default }}">&nbsp;&nbsp;&nbsp;</span></td>
                                <td>
                                    @if($servicio->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('servicios.edit', $servicio) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('servicios.destroy', $servicio) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar este servicio?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No hay servicios cargados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $servicios->links() }}</div>
    </div>
@endsection
