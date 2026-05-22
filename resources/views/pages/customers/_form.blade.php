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
        <label for="city">Stadt</label>
        <input id="city" name="city" class="premium-input" value="{{ old('city', $customer->city) }}">
        @error('city') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="vat_number">USt-Nummer</label>
        <input id="vat_number" name="vat_number" class="premium-input" value="{{ old('vat_number', $customer->vat_number) }}">
        @error('vat_number') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field full">
        <label for="delivery_address">Lieferadresse</label>
        <textarea id="delivery_address" name="delivery_address" rows="4" class="premium-textarea">{{ old('delivery_address', $customer->delivery_address) }}</textarea>
        @error('delivery_address') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field full">
        <label for="billing_address">Rechnungsadresse</label>
        <textarea id="billing_address" name="billing_address" rows="4" class="premium-textarea">{{ old('billing_address', $customer->billing_address) }}</textarea>
        @error('billing_address') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

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
</div>
