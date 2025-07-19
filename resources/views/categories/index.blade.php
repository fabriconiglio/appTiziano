@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="fs-4 fw-bold mb-0">Categorías</h2>
                    <a href="{{ route('categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Categoría
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
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Tipo de Módulo</th>
                                <th>Marcas</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ Str::limit($category->description, 50) }}</td>
                                    <td>{{ ucfirst($category->module_type) }}</td>
                                    <td>
                                        @if($category->brands->count() > 0)
                                            <span class="badge bg-info text-white mb-1">
                                                {{ $category->brands->count() }} {{ Str::plural('marca', $category->brands->count()) }}
                                            </span>
                                            <div class="text-muted small">
                                                {{ $category->brands->pluck('name')->take(3)->implode(', ') }}@if($category->brands->count() > 3)...@endif
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">Sin marcas</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $category->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
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
                                    <td colspan="7" class="text-center">No hay categorías registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Mostrando {{ $categories->firstItem() ?? 0 }} a {{ $categories->lastItem() ?? 0 }} de {{ $categories->total() }} resultados
                    </div>
                    <div>
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
