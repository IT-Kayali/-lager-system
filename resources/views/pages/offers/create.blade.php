<x-layouts.premium title="Neues Angebot" subtitle="Kunde auswählen, Produkte hinzufügen und Ware automatisch reservieren.">
    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    @if ($customers->isEmpty())
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            Bitte zuerst einen Kunden anlegen.
        </div>
    @endif

    @if ($products->isEmpty())
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            Bitte zuerst Produkte und Chargen anlegen.
        </div>
    @endif

    <section class="premium-card">
        <form method="POST" action="{{ route('offers.store') }}">
            @csrf

            <div class="premium-form-grid">
                <div class="premium-form-field">
                    <label for="customer_id">Kunde *</label>
                    <select id="customer_id" name="customer_id" class="premium-select" required>
                        <option value="">Kunde auswählen</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>
                                {{ $customer->customer_number }} — {{ $customer->company_name }} — {{ $customer->group?->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field">
                    <label for="template_type">PDF-Vorlage *</label>
                    <select id="template_type" name="template_type" class="premium-select" required>
                        @foreach ($templates as $value => $label)
                            <option value="{{ $value }}" @selected(old('template_type', 'with_company') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('template_type') <div class="premium-error">{{ $message }}</div> @enderror
                </div>

                <div class="premium-form-field full">
                    <label for="notes">Notizen optional</label>
                    <textarea id="notes" name="notes" rows="3" class="premium-textarea">{{ old('notes') }}</textarea>
                    @error('notes') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-top:24px;">
                <h2 style="font-size:20px; font-weight:900; margin:0 0 12px;">Produktpositionen</h2>
                <p class="premium-muted" style="margin-top:0;">
                    Preis wird beim Speichern automatisch über Kundengruppe + Gewichtsstufe berechnet.
                    Reservierung erfolgt sofort nach erfolgreicher Prüfung.
                </p>
            </div>

            <div class="premium-table-wrap">
                <table class="premium-table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Menge/Gewicht in Gramm</th>
                            <th>Hinweis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 8; $i++)
                            <tr>
                                <td>
                                    <select name="items[{{ $i }}][product_id]" class="premium-select" style="min-width:320px;">
                                        <option value="">Produkt auswählen</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected((string) old("items.$i.product_id") === (string) $product->id)>
                                                {{ $product->product_code }} — {{ $product->name }}
                                                | Verfügbar: {{ number_format($product->available_stock, 3, ',', '.') }}
                                                | Max. reservierbar: {{ number_format($product->max_reservable, 3, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("items.$i.product_id") <div class="premium-error">{{ $message }}</div> @enderror
                                </td>
                                <td>
                                    <input
                                        name="items[{{ $i }}][quantity]"
                                        type="number"
                                        step="0.001"
                                        min="0.001"
                                        max="5000"
                                        class="premium-input"
                                        style="max-width:220px;"
                                        value="{{ old("items.$i.quantity") }}"
                                        placeholder="z. B. 50, 100, 250"
                                    >
                                    @error("items.$i.quantity") <div class="premium-error">{{ $message }}</div> @enderror
                                </td>
                                <td>
                                    <span class="premium-muted">
                                        50–90g = 50g, 91–239g = 100g, 240–460g = 250g, 461–750g = 500g, 751–5000g = 1000g
                                    </span>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
                <button class="premium-btn gold" type="submit">
                    <i class="bi bi-check2-circle"></i>
                    Angebot erstellen & reservieren
                </button>

                <a href="{{ route('offers.index') }}" class="premium-btn">
                    <i class="bi bi-arrow-left"></i>
                    Zurück
                </a>
            </div>
        </form>
    </section>
</x-layouts.premium>
