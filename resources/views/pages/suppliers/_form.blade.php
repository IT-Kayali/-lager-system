@csrf

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label>Firmenname / Lieferant *</label>
        <input name="company_name" class="premium-input" value="{{ old('company_name', $supplier->company_name) }}" required>
        @error('company_name') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label>Ansprechpartner</label>
        <input name="contact_person" class="premium-input" value="{{ old('contact_person', $supplier->contact_person) }}">
        @error('contact_person') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field full">
        <label>E-Mail</label>
        <input name="email" type="email" class="premium-input" value="{{ old('email', $supplier->email) }}">
        @error('email') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field full">
        <label>Telefon / WhatsApp</label>

        <div class="phone-combo">
            <div>
                <x-country-code-select
                    name="phone_country_code"
                    :selected="$supplier->phone_country_code ?: '+49|DE'"
                />
                @error('phone_country_code') <div class="premium-error">{{ $message }}</div> @enderror
            </div>

            <div>
                <input
                    name="phone"
                    class="premium-input"
                    value="{{ old('phone', $supplier->phone) }}"
                    placeholder="Telefonnummer ohne Vorwahl"
                >
                @error('phone') <div class="premium-error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
</div>

<div class="premium-card" style="box-shadow:none; margin-top:22px;">
    <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Adresse</h2>

    <div class="premium-form-grid">
        <div class="premium-form-field">
            <label>Straße</label>
            <input name="street" class="premium-input" value="{{ old('street', $supplier->street) }}">
            @error('street') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>Hausnummer</label>
            <input name="house_number" class="premium-input" value="{{ old('house_number', $supplier->house_number) }}">
            @error('house_number') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>PLZ</label>
            <input name="postal_code" class="premium-input" value="{{ old('postal_code', $supplier->postal_code) }}">
            @error('postal_code') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>Stadt</label>
            <input name="city" class="premium-input" value="{{ old('city', $supplier->city) }}">
            @error('city') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field full">
            <label>Land</label>
            <input name="country" class="premium-input" value="{{ old('country', $supplier->country ?: 'Deutschland') }}">
            @error('country') <div class="premium-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="premium-form-grid" style="margin-top:22px;">
    <div class="premium-form-field full">
        <label>Notizen optional</label>
        <textarea name="notes" rows="5" class="premium-textarea">{{ old('notes', $supplier->notes) }}</textarea>
        @error('notes') <div class="premium-error">{{ $message }}</div> @enderror
    </div>
</div>

<div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('suppliers.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>
