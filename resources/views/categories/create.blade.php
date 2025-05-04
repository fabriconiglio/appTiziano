@extends('layouts.app')

@section('title', 'Crear Categoría')

@section('content')

    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-4 border-b border-gray-200">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fs-4 fw-bold">Nueva Categoría</h2>
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>

            <div class="p-4">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    @include('categories.form')

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            Guardar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#brands').select2({
                placeholder: 'Selecciona las marcas',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron resultados";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
        });
    </script>
@endpush
