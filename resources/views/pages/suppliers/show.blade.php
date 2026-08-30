<x-layouts.premium title="Lieferantenvorschau" subtitle="Stammdaten des Lieferanten und zugehörige Produkte.">
    @php
        $products = $products ?? collect();

        $statusLabels = [
            'ok' => 'OK',
            'low' => 'Niedrig',
            'critical' => 'Kritisch',
        ];
    @endphp

    <section class="supplier-preview-hero">
        <div>
            <div class="supplier-preview-kicker">Lieferant</div>
            <h1>{{ $supplier->company_name }}</h1>

            <div class="supplier-preview-meta">
                <span><i class="bi bi-upc-scan"></i> {{ $supplier->supplier_number }}</span>

                @if ($supplier->contact_person)
                    <span><i class="bi bi-person"></i> {{ $supplier->contact_person }}</span>
                @endif

                @if ($supplier->email)
                    <span><i class="bi bi-envelope"></i> {{ $supplier->email }}</span>
                @endif

                @if ($supplier->phone)
                    <span><i class="bi bi-telephone"></i> {{ $supplier->phone }}</span>
                @endif

                @if ($supplier->whatsapp ?: $supplier->phone)
                    <span>
                        <x-whatsapp-link :number="$supplier->whatsapp ?: $supplier->phone" label="WhatsApp" :country-code="$supplier->phone_country_code" />
                    </span>
                @endif
            </div>
        </div>

        <span class="premium-badge ok">
            {{ $products->count() }} Produkte
        </span>
    </section>

    <section class="supplier-preview-actions">
        <a class="premium-btn gold" href="{{ route('suppliers.edit', $supplier) }}">
            <i class="bi bi-pencil"></i>
            Bearbeiten
        </a>

        <a class="premium-btn" href="{{ route('suppliers.index') }}">
            <i class="bi bi-arrow-left"></i>
            Zurück
        </a>
    </section>

    <section class="preview-grid">
        <div class="premium-card">
            <h2 class="preview-title">Stammdaten</h2>

            <div class="details-grid">
                <div>
                    <span>Lieferantennummer</span>
                    <strong>{{ $supplier->supplier_number }}</strong>
                </div>

                <div>
                    <span>Firma</span>
                    <strong>{{ $supplier->company_name }}</strong>
                </div>

                <div>
                    <span>Ansprechpartner</span>
                    <strong>{{ $supplier->contact_person ?: '—' }}</strong>
                </div>

                <div>
                    <span>E-Mail</span>
                    <strong>{{ $supplier->email ?: '—' }}</strong>
                </div>

                <div>
                    <span>Telefon</span>
                    <strong>{{ $supplier->phone ?: '—' }}</strong>
                </div>

                <div>
                    <span>WhatsApp</span>
                    <strong>
                        @if ($supplier->whatsapp ?: $supplier->phone)
                            <x-whatsapp-link :number="$supplier->whatsapp ?: $supplier->phone" label="WhatsApp öffnen" :country-code="$supplier->phone_country_code" />
                        @else
                            —
                        @endif
                    </strong>
                </div>

                <div>
                    <span>Produkte</span>
                    <strong>{{ $products->count() }}</strong>
                </div>
            </div>
        </div>

        <div class="premium-card">
            <h2 class="preview-title">Adresse</h2>

            <div class="address-box">
                @if (method_exists($supplier, 'fullAddress') && $supplier->fullAddress())
                    {!! nl2br(e($supplier->fullAddress())) !!}
                @else
                    —
                @endif
            </div>
        </div>
    </section>

    <section class="premium-card">
        <div class="section-head">
            <div>
                <h2 class="preview-title">Zugehörige Produkte</h2>
                <p class="premium-muted" style="margin:4px 0 0;">
                    Alle Produkte, die diesem Lieferanten zugeordnet sind.
                </p>
            </div>

            <a class="premium-btn gold" href="{{ route('products.create') }}">
                <i class="bi bi-plus-lg"></i>
                Produkt hinzufügen
            </a>
        </div>

        <div class="premium-table-wrap">
            <table class="premium-table supplier-products-table">
                <thead>
                    <tr>
                        <th>Produktbezeichnung</th>
                        <th>Fake Name</th>
                        <th>Code-Nummer</th>
                        <th>Verfügbare Menge</th>
                        <th>Status</th>
                        <th>Aktion</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($products as $product)
                        @php
                            $unitLabel = $product->unitLabel('de');
                        @endphp

                        <tr>
                            <td>
                                <a class="product-link" href="{{ route('products.show', ['product' => $product->name]) }}">
                                    {{ $product->name }}
                                </a>
                            </td>

                            <td>{{ $product->manufacturer_designation ?: '—' }}</td>
                            <td>{{ $product->serial_number ?: '—' }}</td>

                            <td>
                                <strong>{{ number_format((float) $product->available_stock, 2, ',', '.') }}</strong>
                                <span class="unit-small">{{ $unitLabel }}</span>
                            </td>

                            <td>
                                <span class="premium-badge {{ $product->stock_status }}">
                                    {{ $statusLabels[$product->stock_status] ?? $product->stock_status }}
                                </span>
                            </td>

                            <td>
                                <a class="premium-icon-btn" href="{{ route('products.show', ['product' => $product->name]) }}" title="Produkt öffnen">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="premium-muted">Diesem Lieferanten sind noch keine Produkte zugeordnet.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <style>
        .supplier-preview-hero {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            background: #ffffff;
            border: 1px solid #e3dacb;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(0,0,0,.05);
            margin-bottom: 14px;
        }

        .supplier-preview-kicker {
            color: #a9871f;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .supplier-preview-hero h1 {
            font-size: 34px;
            font-weight: 950;
            margin: 0;
            color: #111111;
        }

        .supplier-preview-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            margin-top: 12px;
            color: #6f665a;
            font-weight: 800;
        }

        .supplier-preview-meta span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .supplier-preview-meta i {
            color: #a9871f;
        }

        .supplier-preview-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: 1.4fr .7fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        .preview-title {
            font-size: 20px;
            font-weight: 950;
            margin: 0 0 14px;
            color: #111111;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(160px, 1fr));
            gap: 12px;
        }

        .details-grid div,
        .address-box {
            background: #fffdf8;
            border: 1px solid #eadfcd;
            border-radius: 18px;
            padding: 14px;
        }

        .details-grid span {
            display: block;
            color: #7b7164;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .details-grid strong {
            color: #111111;
            font-weight: 950;
        }

        .address-box {
            min-height: 104px;
            line-height: 1.55;
            font-weight: 800;
            color: #111111;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .supplier-products-table {
            min-width: 900px;
        }

        .product-link {
            color: #111111;
            font-weight: 950;
            text-decoration: none;
        }

        .product-link:hover {
            color: #a9871f;
            text-decoration: underline;
        }

        .unit-small {
            color: #7a7064;
            font-size: 12px;
            font-weight: 800;
            margin-left: 4px;
        }

        @media (max-width: 1100px) {
            .preview-grid {
                grid-template-columns: 1fr;
            }

            .details-grid {
                grid-template-columns: repeat(2, minmax(150px, 1fr));
            }
        }

        @media (max-width: 700px) {
            .supplier-preview-hero,
            .section-head {
                display: grid;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .supplier-preview-hero h1 {
                font-size: 28px;
            }
        }
    </style>
</x-layouts.premium>
