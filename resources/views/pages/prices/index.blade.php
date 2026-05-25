<x-layouts.premium title="Preise" subtitle="Preisstaffeln pro Produkt, Kundengruppe und Gewichtsstufe.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    <section class="premium-card">
        <form method="GET" action="{{ route('prices.index') }}" class="premium-toolbar">
            <div class="premium-search">
                <select name="product_id" class="premium-select" style="min-width: 280px;" onchange="this.form.submit()">
                    @forelse ($products as $product)
                        <option value="{{ $product->id }}" @selected($selectedProduct?->id === $product->id)>
                            {{ $product->name }}
                        </option>
                    @empty
                        <option value="">Noch keine Produkte vorhanden</option>
                    @endforelse
                </select>

                <select name="customer_group_id" class="premium-select" style="min-width: 190px;" onchange="this.form.submit()">
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" @selected($selectedGroup?->id === $group->id)>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>

                <button class="premium-btn" type="submit">
                    <i class="bi bi-arrow-repeat"></i>
                    Anzeigen
                </button>
            </div>
        </form>

        @if (! $selectedProduct)
            <div class="premium-placeholder">
                Bitte zuerst ein Produkt anlegen. Danach werden automatisch leere Preisstaffeln erstellt.
            </div>
        @else
            <form method="POST" action="{{ route('prices.update') }}">
                @csrf
                @method('PUT')

                <input type="hidden" name="product_id" value="{{ $selectedProduct->id }}">
                <input type="hidden" name="customer_group_id" value="{{ $selectedGroup->id }}">

                <div class="premium-table-wrap">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Produkt</th>
                                <th>Kundengruppe</th>
                                <th>Gewichtsbereich</th>
                                <th>Preisstufe</th>
                                <th>Preis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tiers as $tier)
                                <tr>
                                    <td>
                                        <strong>{{ $selectedProduct->name }}</strong>
                                        <div class="premium-muted">{{ $selectedProduct->product_code }}</div>
                                    </td>
                                    <td>
                                        <span class="premium-badge ok">{{ $selectedGroup->name }}</span>
                                    </td>
                                    <td>{{ $tier->min_grams }}–{{ $tier->max_grams }} Gramm</td>
                                    <td><span class="premium-code">{{ $tier->tier_label }}</span></td>
                                    <td>
                                        <input
                                            name="prices[{{ $tier->id }}]"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="premium-input"
                                            style="max-width: 180px;"
                                            value="{{ old('prices.' . $tier->id, $tier->price) }}"
                                        >
                                        @error('prices.' . $tier->id)
                                            <div class="premium-error">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (auth()->user()?->isManager())
                    <div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
                        <button class="premium-btn gold" type="submit">
                            <i class="bi bi-save"></i>
                            Alle Preise speichern
                        </button>
                    </div>
                @else
                    <div class="premium-placeholder" style="margin-top:18px;">
                        Du kannst Preise sehen, aber nur Manager dürfen Preise ändern.
                    </div>
                @endif
            </form>
        @endif
    </section>
</x-layouts.premium>
