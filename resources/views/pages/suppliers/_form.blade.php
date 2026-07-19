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

<div class="premium-card supplier-address-card">
    <h2>Adresse</h2>

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

<div class="supplier-form-actions">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('suppliers.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>


<style>
    /* SUPPLIER_EDITOR_UI_REFRESH_START */
    .supplier-editor-card {
        border-radius: 20px !important;
        border-color: #d9c9ae !important;
        background: rgba(255, 255, 255, .9) !important;
        box-shadow: 0 18px 50px rgba(33, 29, 23, .08) !important;
    }

    .supplier-editor-form > .premium-form-grid:first-of-type {
        padding: 4px 0 2px;
    }

    .supplier-editor-form .premium-form-field label {
        color: #211d17 !important;
        font-size: 12px !important;
        font-weight: 950 !important;
        text-transform: uppercase;
        letter-spacing: .045em;
    }

    .supplier-address-card {
        margin-top: 24px !important;
        padding: 22px !important;
        border: 1px solid #e0d1b7 !important;
        background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%) !important;
        box-shadow: none !important;
    }

    .supplier-address-card h2 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 18px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e7dece;
        color: #111;
        font-size: 21px;
        font-weight: 950;
        letter-spacing: -.02em;
    }

    .supplier-address-card h2::before {
        content: "\F3E8";
        width: 34px;
        height: 34px;
        display: inline-grid;
        place-items: center;
        border-radius: 11px;
        background: #f3e8be;
        color: #7b5c00;
        font-family: bootstrap-icons !important;
        font-size: 16px;
        font-weight: 400;
        line-height: 1;
    }

    .supplier-form-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 22px;
        border-top: 1px solid #e7dece;
        flex-wrap: wrap;
    }

    .supplier-form-actions .premium-btn {
        min-width: 150px;
        justify-content: center;
    }

    @media (max-width: 780px) {
        .supplier-form-actions .premium-btn {
            width: 100%;
        }
    }
    /* SUPPLIER_EDITOR_UI_REFRESH_END */
</style>
