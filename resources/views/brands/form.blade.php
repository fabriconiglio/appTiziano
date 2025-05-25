<input type="hidden" name="is_active" value="0">
<input type="checkbox"
       class="form-check-input @error('is_active') is-invalid @enderror"
       id="is_active"
       name="is_active"
       value="1"
       {{ old('is_active', $brand->is_active ?? true) ? 'checked' : '' }}> 