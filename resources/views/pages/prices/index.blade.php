<x-layouts.premium title="Preise" subtitle="Preisstaffeln pro Produkt, Kundengruppe und Gewichtsstufe verwalten.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    <section class="prices-toolbar-card">
        <form method="GET" action="{{ route('prices.index') }}" class="prices-filter-form">
            <div class="prices-select-field wide">
                <label>Produkt</label>
                <select name="product_id" class="premium-select" onchange="this.form.submit()">
                    @forelse ($products as $product)
                        <option value="{{ $product->id }}" @selected($selectedProduct?->id === $product->id)>
                            {{ $product->name }}{{ $product->product_code ? ' · ' . $product->product_code : '' }}
                        </option>
                    @empty
                        <option value="">Noch keine Produkte vorhanden</option>
                    @endforelse
                </select>
            </div>

            <div class="prices-select-field">
                <label>Kundengruppe</label>
                <select name="customer_group_id" class="premium-select" onchange="this.form.submit()">
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" @selected($selectedGroup?->id === $group->id)>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="premium-btn dark" type="submit">
                <i class="bi bi-arrow-repeat"></i>
                Anzeigen
            </button>
        </form>
    </section>

    @if (! $selectedProduct)
        <section class="prices-empty-card">
            <i class="bi bi-tags"></i>
            <strong>Bitte zuerst ein Produkt anlegen.</strong>
            <span>Danach werden automatisch leere Preisstaffeln erstellt.</span>
        </section>
    @else
        <form method="POST" action="{{ route('prices.update') }}" class="prices-editor-form">
            @csrf
            @method('PUT')

            <input type="hidden" name="product_id" value="{{ $selectedProduct->id }}">
            <input type="hidden" name="customer_group_id" value="{{ $selectedGroup->id }}">

            <section class="prices-summary-grid">
                <div class="prices-summary-card">
                    <span class="prices-summary-icon"><i class="bi bi-box-seam"></i></span>
                    <div>
                        <span>Produkt</span>
                        <strong>{{ $selectedProduct->name }}</strong>
                        <small>{{ $selectedProduct->product_code ?: 'Ohne Code' }}</small>
                    </div>
                </div>

                <div class="prices-summary-card">
                    <span class="prices-summary-icon"><i class="bi bi-people"></i></span>
                    <div>
                        <span>Kundengruppe</span>
                        <strong>{{ $selectedGroup->name }}</strong>
                        <small>{{ $tiers->count() }} Preisstaffeln</small>
                    </div>
                </div>

                <div class="prices-summary-card dark">
                    <span class="prices-summary-icon"><i class="bi bi-currency-euro"></i></span>
                    <div>
                        <span>Preispflege</span>
                        <strong>{{ auth()->user()?->isManager() ? 'Bearbeiten aktiv' : 'Nur Ansicht' }}</strong>
                        <small>{{ auth()->user()?->isManager() ? 'Änderungen können gespeichert werden' : 'Nur Manager dürfen speichern' }}</small>
                    </div>
                </div>
            </section>

            <section class="prices-table-card">
                <div class="prices-table-header">
                    <div>
                        <h2>Preisstaffeln</h2>
                        <p>Pflege die Verkaufspreise je Gewichtsstufe für diese Kundengruppe.</p>
                    </div>

                    @if (auth()->user()?->isManager())
                        <button class="premium-btn gold" type="submit">
                            <i class="bi bi-save"></i>
                            Alle Preise speichern
                        </button>
                    @endif
                </div>

                <div class="premium-table-wrap prices-table-wrap">
                    <table class="premium-table prices-table">
                        <thead>
                            <tr>
                                <th>Gewichtsbereich</th>
                                <th>Preisstufe</th>
                                <th>Kundengruppe</th>
                                <th>Preis</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($tiers as $tier)
                                <tr>
                                    <td>
                                        <strong>{{ $tier->min_grams }}–{{ $tier->max_grams }} Gramm</strong>
                                        <div class="premium-muted">{{ $selectedProduct->name }}</div>
                                    </td>

                                    <td>
                                        <span class="prices-tier-pill">{{ $tier->tier_label }}</span>
                                    </td>

                                    <td>
                                        <span class="prices-group-pill">{{ $selectedGroup->name }}</span>
                                    </td>

                                    <td>
                                        <div class="prices-input-wrap">
                                            <span>€</span>
                                            <input
                                                name="prices[{{ $tier->id }}]"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="premium-input prices-price-input"
                                                value="{{ old('prices.' . $tier->id, $tier->price) }}"
                                                @disabled(! auth()->user()?->isManager())
                                            >
                                        </div>

                                        @error('prices.' . $tier->id)
                                            <div class="premium-error">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @unless (auth()->user()?->isManager())
                    <div class="prices-readonly-note">
                        <i class="bi bi-info-circle"></i>
                        Du kannst Preise sehen, aber nur Manager dürfen Preise ändern.
                    </div>
                @endunless
            </section>
        </form>
    @endif

    <style>
        .prices-toolbar-card,
        .prices-table-card,
        .prices-empty-card,
        .prices-summary-card {
            background: rgba(255, 255, 255, .88);
            border: 1px solid #d9c9ae;
            border-radius: 18px;
            box-shadow: 0 18px 48px rgba(33, 29, 23, .08);
        }

        .prices-toolbar-card {
            padding: 18px 20px;
            margin-bottom: 22px;
        }

        .prices-filter-form {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
        }

        .prices-select-field {
            display: grid;
            gap: 7px;
            min-width: 220px;
        }

        .prices-select-field.wide {
            min-width: min(520px, 100%);
            flex: 1;
        }

        .prices-select-field label {
            color: #211d17;
            font-size: 12px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .045em;
        }

        .prices-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .prices-summary-card {
            display: flex;
            gap: 14px;
            align-items: center;
            padding: 20px;
        }

        .prices-summary-card.dark {
            background: #121212;
            color: #fff;
            border-color: #121212;
        }

        .prices-summary-icon {
            width: 46px;
            height: 46px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: #f3e8be;
            color: #7b5c00;
            font-size: 20px;
            flex: 0 0 auto;
        }

        .prices-summary-card span:not(.prices-summary-icon) {
            display: block;
            color: #6f665b;
            font-size: 12px;
            font-weight: 950;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .prices-summary-card.dark span:not(.prices-summary-icon) {
            color: #d9c9ae;
        }

        .prices-summary-card strong {
            display: block;
            margin-top: 5px;
            color: inherit;
            font-size: 20px;
            font-weight: 950;
            line-height: 1.15;
        }

        .prices-summary-card small {
            display: block;
            margin-top: 4px;
            color: #7a7064;
            font-size: 13px;
            font-weight: 800;
        }

        .prices-summary-card.dark small {
            color: #f3e8be;
        }

        .prices-table-card {
            overflow: hidden;
        }

        .prices-table-header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 22px 24px 18px;
            border-bottom: 1px solid #e7dece;
        }

        .prices-table-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 950;
            letter-spacing: -.02em;
            color: #121212;
        }

        .prices-table-header p {
            margin: 4px 0 0;
            color: #6f665b;
            font-size: 14px;
            font-weight: 700;
        }

        .prices-table-wrap {
            margin-top: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
            border-radius: 0 !important;
        }

        .prices-table {
            min-width: 820px;
        }

        .prices-tier-pill,
        .prices-group-pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid #d7c7ab;
            background: #f4efe5;
            color: #3d352b;
            font-size: 13px;
            font-weight: 900;
        }

        .prices-group-pill {
            background: #d5aa1f;
            border-color: rgba(201, 162, 39, .45);
            color: #111;
        }

        .prices-input-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            max-width: 210px;
            width: 100%;
        }

        .prices-input-wrap span {
            display: grid;
            place-items: center;
            width: 36px;
            height: 44px;
            border-radius: 12px;
            background: #f3e8be;
            color: #7b5c00;
            font-weight: 950;
        }

        .prices-price-input {
            max-width: 160px !important;
            font-weight: 900 !important;
        }

        .prices-price-input:disabled {
            opacity: .68;
            cursor: not-allowed;
        }

        .prices-readonly-note {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 16px 22px 20px;
            color: #6f665b;
            font-weight: 800;
            border-top: 1px solid #e7dece;
        }

        .prices-empty-card {
            display: grid;
            place-items: center;
            gap: 8px;
            padding: 42px 22px;
            color: #6f665b;
            text-align: center;
        }

        .prices-empty-card i {
            width: 54px;
            height: 54px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: #f3e8be;
            color: #7b5c00;
            font-size: 24px;
        }

        .prices-empty-card strong {
            color: #111;
            font-size: 19px;
        }

        @media (max-width: 980px) {
            .prices-summary-grid {
                grid-template-columns: 1fr;
            }

            .prices-table-header,
            .prices-filter-form {
                align-items: stretch;
                flex-direction: column;
            }

            .prices-table-header .premium-btn,
            .prices-filter-form .premium-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</x-layouts.premium>
