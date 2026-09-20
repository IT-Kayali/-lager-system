@csrf

@php
    $hasDifferentDeliveryAddress = old('delivery_address_different') !== null
        ? (bool) old('delivery_address_different')
        : collect([
            $customer->delivery_street,
            $customer->delivery_house_number,
            $customer->delivery_postal_code,
            $customer->delivery_city,
            $customer->delivery_country,
        ])->filter(fn ($value) => filled($value))->values()->all()
            !== collect([
                $customer->billing_street,
                $customer->billing_house_number,
                $customer->billing_postal_code,
                $customer->billing_city,
                $customer->billing_country,
            ])->filter(fn ($value) => filled($value))->values()->all();

    $selectedGroupId = (int) old('customer_group_id', $customer->customer_group_id);
    $selectedCustomerGroup = $groups->firstWhere('id', $selectedGroupId);
@endphp

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="company_name">Firmenname / Kundenname *</label>
        <input id="company_name" name="company_name" class="premium-input" value="{{ old('company_name', $customer->company_name) }}" required>
        @error('company_name') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="customer_group_id">Kundengruppe *</label>
        <select id="customer_group_id" name="customer_group_id" class="premium-select" data-search="true" required>
            <option value="">Kundengruppe auswählen</option>
            @foreach ($groups as $group)
                <option
                    value="{{ $group->id }}"
                    data-color="{{ $group->displayColor() }}"
                    data-text-color="{{ $group->textColor() }}"
                    @selected((string) $selectedGroupId === (string) $group->id)
                >
                    {{ $group->name }}
                </option>
            @endforeach
        </select>

        <div id="customer-group-preview-wrap" data-csp-style="s-da44e53d" @unless($selectedCustomerGroup) hidden @endunless>
            <span
                id="customer-group-preview"
                class="premium-badge"
                @if($selectedCustomerGroup) style="{{ $selectedCustomerGroup->badgeStyle() }}" @endif
            >
                {{ $selectedCustomerGroup?->name }}
            </span>
        </div>

        @error('customer_group_id') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="email">E-Mail</label>
        <input id="email" name="email" type="email" class="premium-input" value="{{ old('email', $customer->email) }}">
        @error('email') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field full">
        <label>Telefon / WhatsApp</label>

        <div class="phone-combo">
            <div>
                <x-country-code-select
                    name="phone_country_code"
                    id="phone_country_code"
                    :selected="$customer->phone_country_code ?: '+49|DE'"
                />
                @error('phone_country_code') <div class="premium-error">{{ $message }}</div> @enderror
            </div>

            <div>
                <input
                    id="phone"
                    name="phone"
                    class="premium-input"
                    value="{{ old('phone', $customer->phone) }}"
                    placeholder="Telefonnummer ohne Vorwahl"
                >
                @error('phone') <div class="premium-error">{{ $message }}</div> @enderror
            </div>
        </div>
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

<div class="premium-card" data-csp-style="s-5de67bdf">
    <h2 data-csp-style="s-ff8f5dcf">Rechnungsadresse</h2>

    <div class="premium-form-grid">
        <div class="premium-form-field">
            <label for="billing_street">Straße</label>
            <input id="billing_street" name="billing_street" class="premium-input" value="{{ old('billing_street', $customer->billing_street) }}">
            @error('billing_street') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label for="billing_house_number">Hausnummer</label>
            <input id="billing_house_number" name="billing_house_number" class="premium-input" value="{{ old('billing_house_number', $customer->billing_house_number) }}">
            @error('billing_house_number') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label for="billing_postal_code">PLZ</label>
            <input id="billing_postal_code" name="billing_postal_code" class="premium-input" value="{{ old('billing_postal_code', $customer->billing_postal_code) }}">
            @error('billing_postal_code') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label for="billing_city">Stadt</label>
            <input id="billing_city" name="billing_city" class="premium-input" value="{{ old('billing_city', $customer->billing_city) }}">
            @error('billing_city') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field full">
            <label for="billing_country">Land</label>
            <input id="billing_country" name="billing_country" class="premium-input" value="{{ old('billing_country', $customer->billing_country ?: 'Deutschland') }}">
            @error('billing_country') <div class="premium-error">{{ $message }}</div> @enderror
        </div>
    </div>

    <label for="delivery_address_different" data-csp-style="s-649d84c0">
        <input
            id="delivery_address_different"
            name="delivery_address_different"
            type="checkbox"
            value="1"
            data-customer-form-runtime
            @checked($hasDifferentDeliveryAddress)
            data-csp-style="s-9dc36c61"
        >
        <span>Lieferadresse weicht von der Rechnungsadresse ab</span>
    </label>

    <div class="premium-muted" data-csp-style="s-f773241c">
        Ohne Haken wird die Rechnungsadresse automatisch auch als Lieferadresse gespeichert.
    </div>
</div>

<div id="delivery-address-card" class="premium-card" data-csp-style="s-5de67bdf" @unless($hasDifferentDeliveryAddress) hidden @endunless>
    <h2 data-csp-style="s-ff8f5dcf">Abweichende Lieferadresse</h2>

    <div class="premium-form-grid">
        <div class="premium-form-field">
            <label for="delivery_street">Straße</label>
            <input id="delivery_street" name="delivery_street" class="premium-input" value="{{ old('delivery_street', $customer->delivery_street) }}">
            @error('delivery_street') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label for="delivery_house_number">Hausnummer</label>
            <input id="delivery_house_number" name="delivery_house_number" class="premium-input" value="{{ old('delivery_house_number', $customer->delivery_house_number) }}">
            @error('delivery_house_number') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label for="delivery_postal_code">PLZ</label>
            <input id="delivery_postal_code" name="delivery_postal_code" class="premium-input" value="{{ old('delivery_postal_code', $customer->delivery_postal_code) }}">
            @error('delivery_postal_code') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field">
            <label for="delivery_city">Stadt</label>
            <input id="delivery_city" name="delivery_city" class="premium-input" value="{{ old('delivery_city', $customer->delivery_city) }}">
            @error('delivery_city') <div class="premium-error">{{ $message }}</div> @enderror
        </div>

        <div class="premium-form-field full">
            <label for="delivery_country">Land</label>
            <input id="delivery_country" name="delivery_country" class="premium-input" value="{{ old('delivery_country', $customer->delivery_country ?: 'Deutschland') }}">
            @error('delivery_country') <div class="premium-error">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

<div class="premium-form-grid" data-csp-style="s-055aa442">
    <div class="premium-form-field full">
        <label for="delivery_note_instruction">Dauerhafter Lieferschein-Hinweis</label>
        <textarea
            id="delivery_note_instruction"
            name="delivery_note_instruction"
            rows="3"
            class="premium-textarea"
            placeholder="z. B. Kunde braucht Karton ohne Logo"
        >{{ old('delivery_note_instruction', $customer->delivery_note_instruction) }}</textarea>
        <div class="premium-muted" data-csp-style="s-52706827">
            Wird automatisch auf jedem Lieferschein dieses Kunden unter den Versandangaben angezeigt.
        </div>
        @error('delivery_note_instruction') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field full">
        <label for="notes">Notizen optional</label>
        <textarea id="notes" name="notes" rows="5" class="premium-textarea">{{ old('notes', $customer->notes) }}</textarea>
        @error('notes') <div class="premium-error">{{ $message }}</div> @enderror
    </div>
</div>

<div data-csp-style="s-617a0f78">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('customers.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>