<input type="hidden" name="is_active" value="0">
<input type="checkbox"
       class="form-check-input @error('is_active') is-invalid @enderror"
       id="is_active"
       name="is_active"
       value="1"
       {{ old('is_active', $distributorBrand->is_active ?? true) ? 'checked' : '' }}>
<label class="form-check-label" for="is_active">Marca Activa</label> 