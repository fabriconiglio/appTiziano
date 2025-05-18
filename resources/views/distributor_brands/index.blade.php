@extends('layouts.app')

@section('title', 'Distributor Brands')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fs-4 fw-bold">Distributor Brands</h2>
                    <a href="{{ route('distributor_brands.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Distributor Brand
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
                                <th>Name</th>
                                <th>Description</th>
                                <th>Categories</th>
                                <th>Status</th>
                                <th>Actions</th>
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
                                    <td>{{ $brand->categories->pluck('name')->implode(', ') }}</td>
                                    <td>
                                        <span class="badge {{ $brand->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="d-flex gap-2">
                                        <a href="{{ route('distributor_brands.edit', $brand) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('distributor_brands.destroy', $brand) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this distributor brand?');">
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
                                    <td colspan="6" class="text-center">No distributor brands registered</td>
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