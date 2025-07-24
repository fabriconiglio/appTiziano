@extends('layouts.app')

@section('title', 'Marcas de Distribuidora')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="fs-4 fw-bold mb-0">Marcas de Distribuidora</h2>
                <a href="{{ route('distributor_brands.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Marca de Distribuidora
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Categorías</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brands as $brand)
                                <tr>
                                    <td>
                                        @if($brand->logo_url)
                                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="img-thumbnail me-2" width="50">
                                        @endif
                                        {{ $brand->name }}
                                    </td>
                                    <td>{{ Str::limit($brand->description, 50) }}</td>
                                    <td>
                                        @if($brand->categories->count() > 0)
                                            <span class="badge bg-info text-white mb-1">
                                                {{ $brand->categories->count() }} {{ Str::plural('categoría', $brand->categories->count()) }}
                                            </span>
                                            <div class="text-muted small">
                                                {{ $brand->categories->pluck('name')->take(3)->implode(', ') }}@if($brand->categories->count() > 3)...@endif
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">Sin categorías</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $brand->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $brand->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('distributor_brands.edit', $brand) }}" class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('distributor_brands.destroy', $brand) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta marca de distribuidora?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay marcas de distribuidora registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Mostrando {{ $brands->firstItem() ?? 0 }} a {{ $brands->lastItem() ?? 0 }} de {{ $brands->total() }} resultados
                    </div>
                    <div>
                        {{ $brands->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 