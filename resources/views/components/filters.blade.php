{{-- 
    Componente de Filtros Reutilizable
    
    Parámetros:
    - $route: Ruta para el formulario (requerido)
    - $filters: Array de configuraciones de filtros
    - $showSearch: Mostrar campo de búsqueda (default: true)
    - $searchPlaceholder: Placeholder para el campo de búsqueda
--}}

@props([
    'route' => null,
    'filters' => [],
    'showSearch' => true,
    'searchPlaceholder' => 'Buscar...'
])

@if($route)
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ $route }}" class="row g-3">
            @if($showSearch)
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="{{ $searchPlaceholder }}"
                           value="{{ request('search') }}">
                </div>
            @endif

            @foreach($filters as $filter)
                <div class="col-md-2">
                    @if($filter['type'] === 'select')
                        <select name="{{ $filter['name'] }}" class="form-control form-control-sm">
                            <option value="">{{ $filter['placeholder'] ?? 'Seleccionar...' }}</option>
                            @foreach($filter['options'] as $value => $label)
                                <option value="{{ $value }}" 
                                    {{ request($filter['name']) == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    @elseif($filter['type'] === 'date')
                        <input type="date" name="{{ $filter['name'] }}" 
                               class="form-control form-control-sm"
                               value="{{ request($filter['name']) }}" 
                               placeholder="{{ $filter['placeholder'] ?? '' }}">
                    @elseif($filter['type'] === 'text')
                        <input type="text" name="{{ $filter['name'] }}" 
                               class="form-control form-control-sm"
                               value="{{ request($filter['name']) }}" 
                               placeholder="{{ $filter['placeholder'] ?? '' }}">
                    @endif
                </div>
            @endforeach

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> {{ $showSearch ? 'Buscar' : 'Filtrar' }}
                </button>
                @if(request()->hasAny(array_merge(['search'], array_column($filters, 'name'))))
                    <a href="{{ $route }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Auto-submit para selects (solo si hay filtros de tipo select)
    document.querySelectorAll('select[name]').forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
@endif


