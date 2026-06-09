@csrf

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="name">Kategoriename *</label>
        <input id="name" name="name" class="premium-input" value="{{ old('name', $category->name) }}" required>
        @error('name') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="slug">Slug optional</label>
        <input id="slug" name="slug" class="premium-input" value="{{ old('slug', $category->slug) }}" placeholder="wird automatisch erzeugt">
        @error('slug') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="color">Farbe</label>
        <input id="color" name="color" type="color" class="premium-input" value="{{ old('color', $category->color ?: '#d4af37') }}" style="height:44px;">
        @error('color') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field" style="display:flex; align-items:end;">
        <label style="display:flex; align-items:center; gap:10px; font-weight:800;">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
            Aktiv
        </label>
    </div>

    <div class="premium-form-field full">
        <label for="description">Beschreibung optional</label>
        <textarea id="description" name="description" rows="4" class="premium-textarea">{{ old('description', $category->description) }}</textarea>
        @error('description') <div class="premium-error">{{ $message }}</div> @enderror
    </div>
</div>

<div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        {{ $submitLabel }}
    </button>

    <a href="{{ route('product-categories.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>
