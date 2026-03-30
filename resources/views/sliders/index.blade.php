@extends('layouts.app')

@section('title', 'Sliders')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="fs-4 fw-bold mb-0">
                    <i class="fas fa-images me-2"></i>Sliders del E-Commerce
                </h2>
                <a href="{{ route('sliders.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Slider
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Orden</th>
                                <th style="width: 100px;"><i class="fas fa-desktop me-1"></i> Desktop</th>
                                <th style="width: 100px;"><i class="fas fa-mobile-alt me-1"></i> Móvil</th>
                                <th>Título</th>
                                <th>Etiqueta</th>
                                <th>CTA</th>
                                <th>Estado</th>
                                <th style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sliders as $slider)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $slider->order }}</span>
                                    </td>
                                    <td>
                                        @if($slider->image)
                                            <img src="{{ asset('storage/' . $slider->image) }}"
                                                 alt="{{ $slider->title }}"
                                                 class="rounded"
                                                 style="width: 80px; height: 45px; object-fit: cover;">
                                        @else
                                            <div class="rounded d-flex align-items-center justify-content-center"
                                                 style="width: 80px; height: 45px; background: {{ $slider->bg_color }};">
                                                <i class="fas fa-image text-white opacity-50"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($slider->image_mobile)
                                            <img src="{{ asset('storage/' . $slider->image_mobile) }}"
                                                 alt="{{ $slider->title }} (móvil)"
                                                 class="rounded"
                                                 style="width: 45px; height: 60px; object-fit: cover;">
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $slider->title }}</strong>
                                        @if($slider->subtitle)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($slider->subtitle, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($slider->tag)
                                            <span class="badge bg-info text-white">{{ $slider->tag }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $slider->cta_text }}</small>
                                        <br>
                                        <small class="text-muted">→ {{ $slider->cta_link }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $slider->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $slider->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('sliders.edit', $slider) }}" class="btn btn-sm btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('sliders.destroy', $slider) }}" method="POST"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este slider?');">
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
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-images fa-2x text-muted mb-2 d-block"></i>
                                        No hay sliders creados. <a href="{{ route('sliders.create') }}">Crear el primero</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($sliders->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Mostrando {{ $sliders->firstItem() ?? 0 }} a {{ $sliders->lastItem() ?? 0 }} de {{ $sliders->total() }} resultados
                        </div>
                        <div>
                            {{ $sliders->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
