<div class="mb-3">
    <label for="name" class="form-label">Nombre</label>
    <input type="text"
           class="form-control @error('name') is-invalid @enderror"
           id="name"
           name="name"
           value="{{ old('name', $category->name ?? '') }}"
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Descripción</label>
    <input type="text"
           class="form-control @error('description') is-invalid @enderror"
           id="description"
           name="description"
           value="{{ old('description', $category->description ?? '') }}">
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<input type="hidden" name="module_type" value="peluqueria">

<!-- Nuevo campo para selección de marcas -->
<div class="mb-3">
    <label for="brands" class="form-label">Marcas Asociadas</label>
    <select class="form-select @error('brands') is-invalid @enderror"
            id="brands"
            name="brands[]"
            multiple>
        @foreach($brands as $brand)
            <option value="{{ $brand->id }}"
                {{ in_array($brand->id, old('brands', isset($category) ? $category->brands->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                {{ $brand->name }}
            </option>
        @endforeach
    </select>
    @error('brands')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Puedes seleccionar múltiples marcas manteniendo presionada la tecla Ctrl/Cmd</div>
</div>

<div class="mb-3">
    <input type="hidden" name="is_active" value="0">
    <div class="form-check">
        <input type="checkbox"
               class="form-check-input @error('is_active') is-invalid @enderror"
               id="is_active"
               name="is_active"
               value="1"
               {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
            Categoría Activa
        </label>
        @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
