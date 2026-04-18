<input type="hidden" name="is_active" value="0">
<div class="form-check form-switch mb-3">
    <input type="checkbox"
           class="form-check-input @error('is_active') is-invalid @enderror"
           id="is_active"
           name="is_active"
           value="1"
           {{ old('is_active', $distributorBrand->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Marca Activa</label>
</div>

<input type="hidden" name="is_featured" value="0">
<div class="form-check form-switch">
    <input type="checkbox"
           class="form-check-input @error('is_featured') is-invalid @enderror"
           id="is_featured"
           name="is_featured"
           value="1"
           {{ old('is_featured', $distributorBrand->is_featured ?? false) ? 'checked' : '' }}>
    <label class="form-check-label fw-semibold" for="is_featured">
        <i class="fas fa-star text-warning me-1"></i> Marca destacada en el E-Commerce
    </label>
    <div class="form-text">Si está activo, aparece en la sección de marcas destacadas de la tienda online.</div>
</div>