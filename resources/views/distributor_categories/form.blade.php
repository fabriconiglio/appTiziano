<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text"
           class="form-control @error('name') is-invalid @enderror"
           id="name"
           name="name"
           value="{{ old('name', $distributorCategory->name ?? '') }}"
           required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <input type="text"
           class="form-control @error('description') is-invalid @enderror"
           id="description"
           name="description"
           value="{{ old('description', $distributorCategory->description ?? '') }}">
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="brands" class="form-label">Associated Brands</label>
    <select class="form-select @error('brands') is-invalid @enderror"
            id="brands"
            name="brands[]"
            multiple>
        @foreach($brands as $brand)
            <option value="{{ $brand->id }}"
                {{ in_array($brand->id, old('brands', $selectedBrands ?? ($distributorCategory->brands->pluck('id')->toArray() ?? []))) ? 'selected' : '' }}>
                {{ $brand->name }}
            </option>
        @endforeach
    </select>
    @error('brands')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">You can select multiple brands by holding Ctrl/Cmd</div>
</div>

<div class="mb-3">
    <div class="form-check">
        <input type="checkbox"
               class="form-check-input @error('is_active') is-invalid @enderror"
               id="is_active"
               name="is_active"
               value="1"
               {{ old('is_active', $distributorCategory->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
            Active Category
        </label>
        @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div> 