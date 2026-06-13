<!-- resources/views/peluqueras/index.blade.php -->
@extends('layouts.app')

@section('title', 'Peluqueras')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Peluqueras</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('agenda.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Agenda
                </a>
                <a href="{{ route('peluqueras.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Peluquera
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
                            <th>Color</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($peluqueras as $peluquera)
                            <tr>
                                <td>{{ $peluquera->nombre }}</td>
                                <td><span class="badge" style="background: {{ $peluquera->color }}">&nbsp;&nbsp;&nbsp;</span></td>
                                <td>
                                    @if($peluquera->activo)
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('peluqueras.edit', $peluquera) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('peluqueras.destroy', $peluquera) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta peluquera?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No hay peluqueras cargadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $peluqueras->links() }}</div>
    </div>
@endsection
