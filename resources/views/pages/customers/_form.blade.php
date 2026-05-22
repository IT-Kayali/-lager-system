@csrf

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="company_name">Firmenname / Kundenname *</label>
        <input id="company_name" name="company_name" class="premium-input" value="{{ old('company_name', $customer->company_name) }}" required>
        @error('company_name') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="customer_group_id">Kundengruppe *</label>
        <select id="customer_group_id" name="customer_group_id" class="premium-select" required>
            <option value="">Kundengruppe auswählen</option>
            @foreach ($groups as $group)
                <option value="{{ $group->id }}" @selected((string) old('customer_group_id', $customer->customer_group_id) === (string) $group->id)>
                    {{ $group->name }}
                </option>
            @endforeach
        </select>
        @error('customer_group_id') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="email">E-Mail</label>
        <input id="email" name="email" type="email" class="premium-input" value="{{ old('email', $customer->email) }}">
        @error('email') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="phone">Telefon / WhatsApp</label>
        <input id="phone" name="phone" class="premium-input" value="{{ old('phone', $customer->phone) }}">
        @error('phone') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="vat_number">USt-Nummer</label>
        <input id="vat_number" name="vat_number" class="premium-input" value="{{ old('vat_number', $customer->vat_number) }}">
        @error('vat_number') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="city">Stadt allgemein / Suche</label>
        <input id="city" name="city" class="premium-input" value="{{ old('city', $customer->city) }}">
        @error('city') <div class="premium-error">{{ $message }}</div> @enderror
    </div>
</div>

<div class="premium-card" style="box-shadow:none; margin-top:22px;">
    <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Lieferadresse</h2>

    <div class="premium-form-grid">
        <div class="premium-form-field">
            <label>Straße</label>
            <input name="delivery_street" class="premium-input" value="{{ old('delivery_street', $customer->delivery_street) }}">
            @error('delivery_street') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>Hausnummer</label>
            <input name="delivery_house_number" class="premium-input" value="{{ old('delivery_house_number', $customer->delivery_house_number) }}">
            @error('delivery_house_number') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>PLZ</label>
            <input name="delivery_postal_code" class="premium-input" value="{{ old('delivery_postal_code', $customer->delivery_postal_code) }}">
            @error('delivery_postal_code') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>Stadt</label>
            <input name="delivery_city" class="premium-input" value="{{ old('delivery_city', $customer->delivery_city) }}">
            @error('delivery_city') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field full">
            <label>Land</label>
            <input name="delivery_country" class="premium-input" value="{{ old('delivery_country', $customer->delivery_country ?: 'Deutschland') }}">
            @error('delivery_country') <div class="premium-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="premium-card" style="box-shadow:none; margin-top:22px;">
    <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">Rechnungsadresse</h2>

    <div class="premium-form-grid">
        <div class="premium-form-field">
            <label>Straße</label>
            <input name="billing_street" class="premium-input" value="{{ old('billing_street', $customer->billing_street) }}">
            @error('billing_street') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>Hausnummer</label>
            <input name="billing_house_number" class="premium-input" value="{{ old('billing_house_number', $customer->billing_house_number) }}">
            @error('billing_house_number') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>PLZ</label>
            <input name="billing_postal_code" class="premium-input" value="{{ old('billing_postal_code', $customer->billing_postal_code) }}">
            @error('billing_postal_code') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label>Stadt</label>
            <input name="billing_city" class="premium-input" value="{{ old('billing_city', $customer->billing_city) }}">
            @error('billing_city') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field full">
            <label>Land</label>
            <input name="billing_country" class="premium-input" value="{{ old('billing_country', $customer->billing_country ?: 'Deutschland') }}">
            @error('billing_country') <div class="premium-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="premium-form-grid" style="margin-top:22px;">
    <div class="premium-form-field full">
        <label for="notes">Notizen optional</label>
        <textarea id="notes" name="notes" rows="5" class="premium-textarea">{{ old('notes', $customer->notes) }}</textarea>
        @error('notes') <div class="premium-error">{{ $message }}</div> @enderror
    </div>
</div>

<div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('customers.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
<a href="{{ route('suppliers.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>
