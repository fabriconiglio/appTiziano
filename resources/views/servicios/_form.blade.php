@csrf
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="nombre" class="form-control" required
           value="{{ old('nombre', $servicio->nombre ?? '') }}">
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Duración (minutos)</label>
        <input type="number" name="duracion_minutos" class="form-control" min="5" max="600" required
               value="{{ old('duracion_minutos', $servicio->duracion_minutos ?? 30) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Precio base</label>
        <input type="number" step="0.01" name="precio_base" class="form-control" min="0"
               value="{{ old('precio_base', $servicio->precio_base ?? 0) }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Color</label>
        <input type="color" name="color_default" class="form-control form-control-color"
               value="{{ old('color_default', $servicio->color_default ?? '#3788d8') }}">
    </div>
    <div class="col-md-6 mb-3 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo"
                   {{ old('activo', $servicio->activo ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="activo">Activo</label>
        </div>
    </div>
</div>
