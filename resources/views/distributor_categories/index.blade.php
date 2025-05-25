@extends('layouts.app')

@section('title', 'Categorías de Distribuidora')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fs-4 fw-bold">Categorías de Distribuidora</h2>
                    <a href="{{ route('distributor_categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Categoría de Distribuidora
                    </a>
                </div>
            </div>

            <div class="p-4">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
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
                                    <td>
                                        @if($category->brands->count() > 0)
                                            <span class="badge bg-info">
                                                {{ $category->brands->count() }}
                                                {{ Str::plural('marca', $category->brands->count()) }}
                                            </span>
                                            <div class="small text-muted">
                                                {{ $category->brands->pluck('name')->take(3)->implode(', ') }}
                                                @if($category->brands->count() > 3)
                                                    ...
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-secondary">Sin marcas</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $category->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="d-flex gap-2">
                                        <a href="{{ route('distributor_categories.edit', $category) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('distributor_categories.destroy', $category) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta categoría de distribuidora?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay categorías de distribuidora registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection 