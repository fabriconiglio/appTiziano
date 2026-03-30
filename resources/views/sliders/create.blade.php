@extends('layouts.app')

@section('title', 'Crear Slider')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="fs-4 fw-bold mb-0">
                    <i class="fas fa-plus-circle me-2"></i>Nuevo Slider
                </h2>
                <a href="{{ route('sliders.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('sliders.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('sliders.form')

                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('sliders.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Slider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
    const container = document.getElementById('image-preview-container');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.src = ev.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        container.style.display = 'none';
    }
});

document.getElementById('image_mobile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-mobile-preview');
    const container = document.getElementById('image-mobile-preview-container');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.src = ev.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        container.style.display = 'none';
    }
});

document.getElementById('bg_color_picker').addEventListener('input', function(e) {
    document.getElementById('bg_color').value = e.target.value;
});
document.getElementById('bg_color').addEventListener('input', function(e) {
    document.getElementById('bg_color_picker').value = e.target.value;
});
</script>
@endpush
