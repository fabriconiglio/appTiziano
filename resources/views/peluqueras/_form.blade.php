@csrf
@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

@php
    $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'];
    $horarios = old('horarios', $peluquera->horarios ?? []);
@endphp

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required
               value="{{ old('nombre', $peluquera->nombre ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Color</label>
        <input type="color" name="color" class="form-control form-control-color"
               value="{{ old('color', $peluquera->color ?? '#28a745') }}">
    </div>
</div>

<div class="form-check mb-3">
    <input type="hidden" name="activo" value="0">
    <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo"
           {{ old('activo', $peluquera->activo ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="activo">Activa</label>
</div>

<label class="form-label">Horarios de atención</label>
<table class="table table-sm align-middle">
    <tbody>
        @foreach($dias as $num => $nombre)
            @php $rango = $horarios[$num] ?? null; @endphp
            <tr>
                <td style="width: 120px;">{{ $nombre }}</td>
                <td>
                    <input type="time" name="horarios[{{ $num }}][0]" class="form-control form-control-sm"
                           value="{{ $rango[0] ?? '' }}">
                </td>
                <td class="text-center" style="width: 30px;">a</td>
                <td>
                    <input type="time" name="horarios[{{ $num }}][1]" class="form-control form-control-sm"
                           value="{{ $rango[1] ?? '' }}">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<small class="text-muted d-block mb-3">Dejá vacío un día para marcarlo como cerrado.</small>
