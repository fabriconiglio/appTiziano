<div class="row">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
            <input type="text"
                   class="form-control @error('title') is-invalid @enderror"
                   id="title"
                   name="title"
                   value="{{ old('title', $slider->title ?? '') }}"
                   placeholder="Ej: Hidratación Profunda"
                   required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="subtitle" class="form-label">Subtítulo</label>
            <textarea class="form-control @error('subtitle') is-invalid @enderror"
                      id="subtitle"
                      name="subtitle"
                      rows="2"
                      placeholder="Ej: Tratamientos intensivos para cada tipo de cabello">{{ old('subtitle', $slider->subtitle ?? '') }}</textarea>
            @error('subtitle')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tag" class="form-label">Etiqueta</label>
                    <input type="text"
                           class="form-control @error('tag') is-invalid @enderror"
                           id="tag"
                           name="tag"
                           value="{{ old('tag', $slider->tag ?? '') }}"
                           placeholder="Ej: Nueva Colección">
                    @error('tag')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Texto pequeño que aparece arriba del título.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="order" class="form-label">Orden</label>
                    <input type="number"
                           class="form-control @error('order') is-invalid @enderror"
                           id="order"
                           name="order"
                           value="{{ old('order', $slider->order ?? 0) }}"
                           min="0">
                    @error('order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Menor número = aparece primero.</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="cta_text" class="form-label">Texto del botón</label>
                    <input type="text"
                           class="form-control @error('cta_text') is-invalid @enderror"
                           id="cta_text"
                           name="cta_text"
                           value="{{ old('cta_text', $slider->cta_text ?? 'Ver más') }}"
                           placeholder="Ver más">
                    @error('cta_text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="cta_link" class="form-label">Link del botón</label>
                    <input type="text"
                           class="form-control @error('cta_link') is-invalid @enderror"
                           id="cta_link"
                           name="cta_link"
                           value="{{ old('cta_link', $slider->cta_link ?? '/productos') }}"
                           placeholder="/productos">
                    @error('cta_link')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Imagen escritorio --}}
        <div class="mb-3">
            <label for="image" class="form-label"><i class="fas fa-desktop me-1"></i> Imagen Escritorio</label>
            <input type="file"
                   class="form-control @error('image') is-invalid @enderror"
                   id="image"
                   name="image"
                   accept="image/jpeg,image/png,image/webp">
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">JPG, PNG o WebP. Máx 4MB. Recomendado: 1920x700px (horizontal).</div>

            @if(isset($slider) && $slider->image)
                <div class="mt-3 position-relative" id="current-image-container">
                    <img src="{{ asset('storage/' . $slider->image) }}"
                         alt="Imagen escritorio actual"
                         class="img-fluid rounded border"
                         style="max-height: 200px;">
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="delete_image" name="delete_image" value="1">
                        <label class="form-check-label text-danger small" for="delete_image">
                            <i class="fas fa-trash me-1"></i> Eliminar imagen escritorio
                        </label>
                    </div>
                </div>
            @endif

            <div class="mt-3" id="image-preview-container" style="display: none;">
                <p class="small text-muted mb-1">Vista previa:</p>
                <img id="image-preview" src="" alt="Vista previa" class="img-fluid rounded border" style="max-height: 200px;">
            </div>
        </div>

        {{-- Imagen móvil --}}
        <div class="mb-3">
            <label for="image_mobile" class="form-label"><i class="fas fa-mobile-alt me-1"></i> Imagen Móvil</label>
            <input type="file"
                   class="form-control @error('image_mobile') is-invalid @enderror"
                   id="image_mobile"
                   name="image_mobile"
                   accept="image/jpeg,image/png,image/webp">
            @error('image_mobile')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Opcional. Recomendado: 750x900px (vertical). Si no se sube, se usa la de escritorio.</div>

            @if(isset($slider) && $slider->image_mobile)
                <div class="mt-3 position-relative" id="current-image-mobile-container">
                    <img src="{{ asset('storage/' . $slider->image_mobile) }}"
                         alt="Imagen móvil actual"
                         class="img-fluid rounded border"
                         style="max-height: 200px;">
                    <div class="form-check mt-2">
                        <input type="checkbox" class="form-check-input" id="delete_image_mobile" name="delete_image_mobile" value="1">
                        <label class="form-check-label text-danger small" for="delete_image_mobile">
                            <i class="fas fa-trash me-1"></i> Eliminar imagen móvil
                        </label>
                    </div>
                </div>
            @endif

            <div class="mt-3" id="image-mobile-preview-container" style="display: none;">
                <p class="small text-muted mb-1">Vista previa:</p>
                <img id="image-mobile-preview" src="" alt="Vista previa móvil" class="img-fluid rounded border" style="max-height: 200px;">
            </div>
        </div>

        <div class="mb-3">
            <label for="bg_color" class="form-label">Color de fondo</label>
            <div class="input-group">
                <input type="color"
                       class="form-control form-control-color"
                       id="bg_color_picker"
                       value="{{ old('bg_color', $slider->bg_color ?? '#333333') }}"
                       title="Elegir color de fondo">
                <input type="text"
                       class="form-control @error('bg_color') is-invalid @enderror"
                       id="bg_color"
                       name="bg_color"
                       value="{{ old('bg_color', $slider->bg_color ?? '#333333') }}"
                       placeholder="#333333">
            </div>
            @error('bg_color')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Se usa como fondo si no hay imagen, o detrás de la imagen.</div>
        </div>

        <div class="mb-3">
            <input type="hidden" name="is_active" value="0">
            <div class="form-check form-switch">
                <input type="checkbox"
                       class="form-check-input @error('is_active') is-invalid @enderror"
                       id="is_active"
                       name="is_active"
                       value="1"
                       {{ old('is_active', $slider->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="is_active">
                    Slider Activo
                </label>
                @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>
