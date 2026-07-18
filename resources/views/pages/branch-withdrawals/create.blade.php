<x-layouts.premium title="Filialausgang buchen" subtitle="Ware für lokales Geschäft / Filiale abziehen. Mindestbestand darf hier genutzt werden.">
    <section class="branch-booking-card">
        <form class="branch-booking-form" method="POST" action="{{ route('branch-withdrawals.store') }}">
            @csrf

            <div class="branch-booking-grid">
                <div class="branch-booking-main">
                    <section class="branch-booking-section">
                        <div class="branch-booking-section-head">
                            <span class="branch-booking-section-icon">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </span>

                            <div>
                                <h2>Entnahme buchen</h2>
                                <p>Produkt und Menge auswählen. Die Ware wird per FIFO aus den ältesten Chargen entnommen.</p>
                            </div>
                        </div>

                        <div class="branch-booking-fields">
                            <div class="premium-form-field full">
                                <label for="product_id">Produkt *</label>
                                <select id="product_id" name="product_id" class="premium-select" data-search="true" required>
                                    <option value="">Produkt auswählen</option>
                                    @foreach ($products as $product)
                                        <option
                                            value="{{ $product->id }}"
                                            @selected((string) old('product_id', request('product_id')) === (string) $product->id)
                                        >
                                            {{ $product->name }}
                                            @if (!empty($product->serial_number))
                                                — {{ $product->serial_number }}
                                            @endif
                                            | {{ number_format((float) $product->available_stock, 2, ',', '.') }} verfügbar
                                            | Mindestbestand {{ number_format((float) $product->minimum_stock, 2, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id') <div class="premium-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="premium-form-field">
                                <label for="quantity">Menge *</label>
                                <input
                                    id="quantity"
                                    name="quantity"
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    class="premium-input"
                                    value="{{ old('quantity') }}"
                                    required
                                    placeholder="z. B. 50"
                                >
                                @error('quantity') <div class="premium-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="premium-form-field">
                                <label for="branch_name">Filiale / Zweck</label>
                                <input
                                    id="branch_name"
                                    name="branch_name"
                                    class="premium-input"
                                    value="{{ old('branch_name', 'Lokales Geschäft / Filiale') }}"
                                >
                                @error('branch_name') <div class="premium-error">{{ $message }}</div> @enderror
                            </div>

                            <div class="premium-form-field full">
                                <label for="note">Notiz / Grund *</label>
                                <textarea
                                    id="note"
                                    name="note"
                                    rows="6"
                                    class="premium-textarea"
                                    required
                                    placeholder="Pflicht: z. B. Ware für lokales Geschäft entnommen, Kunde vor Ort, Regalbestand aufgefüllt..."
                                >{{ old('note') }}</textarea>
                                @error('note') <div class="premium-error">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="branch-booking-side">
                    <section class="branch-warning-card">
                        <div class="branch-warning-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                        <h3>Wichtiger Hinweis</h3>

                        <p>
                            Diese Funktion darf den Mindestbestand unterschreiten.
                            Für normale Angebote bleibt der Mindestbestand weiterhin geschützt.
                        </p>

                        <div class="branch-warning-list">
                            <div>
                                <i class="bi bi-check2-circle"></i>
                                FIFO-Entnahme aus ältesten Chargen
                            </div>

                            <div>
                                <i class="bi bi-check2-circle"></i>
                                Benutzer und Notiz werden gespeichert
                            </div>

                            <div>
                                <i class="bi bi-check2-circle"></i>
                                Bestand wird sofort reduziert
                            </div>
                        </div>
                    </section>
                </aside>
            </div>

            <div class="branch-booking-actions">
                <button type="submit" class="premium-btn gold">
                    <i class="bi bi-check2-circle"></i>
                    Filialausgang buchen
                </button>

                <a href="{{ route('branch-withdrawals.index') }}" class="premium-btn">
                    <i class="bi bi-arrow-left"></i>
                    Zurück
                </a>
            </div>
        </form>
    </section>

    <style>
        .branch-booking-card {
            background: transparent;
        }

        .branch-booking-form {
            display: grid;
            gap: 22px;
        }

        .branch-booking-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, 420px);
            gap: 22px;
            align-items: start;
        }

        .branch-booking-section,
        .branch-warning-card {
            border: 1px solid #d8cbb7;
            border-radius: 22px;
            background: rgba(255, 255, 255, .88);
            box-shadow: 0 18px 45px rgba(42, 36, 25, .08);
            padding: 26px;
        }

        .branch-booking-section-head {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding-bottom: 18px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e7dece;
        }

        .branch-booking-section-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: #f3e8be;
            color: #111111;
            font-size: 22px;
        }

        .branch-booking-section-head h2 {
            margin: 0;
            color: #111111;
            font-size: 24px;
            font-weight: 950;
            letter-spacing: -.035em;
        }

        .branch-booking-section-head p {
            margin: 6px 0 0;
            color: #665f54;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
        }

        .branch-booking-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .branch-booking-fields .full {
            grid-column: 1 / -1;
        }

        .branch-booking-form label {
            display: inline-flex;
            margin-bottom: 9px;
            color: #111111;
            font-size: 14px;
            font-weight: 900;
        }

        .branch-booking-form .premium-input,
        .branch-booking-form .premium-select,
        .branch-booking-form .premium-textarea {
            width: 100%;
            min-height: 52px;
            border: 1px solid #c9b895 !important;
            border-radius: 14px !important;
            background: #fffdf8 !important;
            color: #111111 !important;
            font-size: 16px;
            font-weight: 750;
        }

        .branch-booking-form .premium-textarea {
            min-height: 150px;
            resize: vertical;
        }

        .branch-warning-card {
            position: sticky;
            top: 96px;
            background:
                radial-gradient(circle at 90% 10%, rgba(212, 170, 32, .18), transparent 32%),
                #2d2b25;
            color: #ffffff;
            border-color: rgba(255, 232, 169, .18);
        }

        .branch-warning-icon {
            width: 58px;
            height: 58px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: #d4aa20;
            color: #111111;
            font-size: 26px;
        }

        .branch-warning-card h3 {
            margin: 18px 0 10px;
            font-size: 25px;
            font-weight: 950;
            letter-spacing: -.04em;
        }

        .branch-warning-card p {
            margin: 0;
            color: rgba(255,255,255,.76);
            font-size: 14px;
            font-weight: 750;
            line-height: 1.5;
        }

        .branch-warning-list {
            display: grid;
            gap: 12px;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,.12);
        }

        .branch-warning-list div {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,.88);
            font-weight: 800;
        }

        .branch-warning-list i {
            color: #ffe690;
        }

        .branch-booking-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .branch-booking-actions .premium-btn {
            min-width: 190px;
        }

        @media (max-width: 1150px) {
            .branch-booking-grid {
                grid-template-columns: 1fr;
            }

            .branch-warning-card {
                position: static;
            }
        }

        @media (max-width: 700px) {
            .branch-booking-section,
            .branch-warning-card {
                padding: 20px;
            }

            .branch-booking-fields {
                grid-template-columns: 1fr;
            }

            .branch-booking-actions {
                display: grid;
            }

            .branch-booking-actions .premium-btn {
                width: 100%;
            }
        }
    </style>
</x-layouts.premium>
