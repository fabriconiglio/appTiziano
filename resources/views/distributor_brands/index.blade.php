@extends('layouts.app')

@section('title', 'Marcas de Distribuidora')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fs-4 fw-bold">Marcas de Distribuidora</h2>
                    <a href="{{ route('distributor_brands.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nueva Marca de Distribuidora
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
                                <th>Categorías</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brands as $brand)
                                <tr>
                                    <td>{{ $brand->id }}</td>
                                    <td>
                                        @if($brand->logo_url)
                                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="img-thumbnail" width="50">
                                        @endif
                                        {{ $brand->name }}
                                    </td>
                                    <td>{{ Str::limit($brand->description, 50) }}</td>
                                    <td>{{ $brand->categories->pluck('name')->implode(', ') ?: 'Sin categorías' }}</td>
                                    <td>
                                        <span class="badge {{ $brand->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $brand->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="d-flex gap-2">
                                        <a href="{{ route('distributor_brands.edit', $brand) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('distributor_brands.destroy', $brand) }}"
                                              method="POST"
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta marca de distribuidora?');">
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
                                    <td colspan="6" class="text-center">No hay marcas de distribuidora registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $brands->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection 