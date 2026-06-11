<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <style>

        @font-face {
            font-family: 'ExoPDF';
            font-style: normal;
            font-weight: 400;
            src: url("file://{{ public_path('fonts/exo/Exo-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'ExoPDF';
            font-style: normal;
            font-weight: 700;
            src: url("file://{{ public_path('fonts/exo/Exo-Bold.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'ExoPDF';
            font-style: normal;
            font-weight: 900;
            src: url("file://{{ public_path('fonts/exo/Exo-Black.ttf') }}") format("truetype");
        }

        @page {
            margin: 0;
            size: A4 portrait;
        }
}") format("truetype");
        }
}") format("truetype");
        }
}") format("truetype");
        }
}") format("truetype");
        }
}") format("truetype");
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #111111;
            font-size: 10pt;
            line-height: 1.25;
        }

        body.font-logo,
        body.font-logo * {
            font-family: 'ExoPDF', DejaVu Sans, sans-serif !important;
        }

        body.font-no-logo,
        body.font-no-logo * {
            font-family: 'GlacialIndifference', DejaVu Sans, sans-serif !important;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            page-break-after: always;
        }

        .page.last-page {
            page-break-after: auto;
        }

        .logo {
            display: block;
            width: auto;
            max-width: 43mm;
            max-height: 17mm;
            object-fit: contain;
            margin-left: -7mm;
            margin-bottom: 3mm;
        }

        .company-block {
            position: absolute;
            top: 29mm;
            left: 27mm;
            width: 70mm;
            font-size: 10pt;
            line-height: 1.22;
            color: #111111;
            text-align: left;
            font-weight: 400;
        }

        .logo-wrap {
            display: block;
            width: 70mm;
            margin-bottom: 3mm;
            text-align: left;
            clear: both;
        }

        .company-details {
            display: block;
            width: 70mm;
            clear: both;
            text-align: left;
            margin-top: 0;
            font-size: 10pt;
            line-height: 1.22;
            font-weight: 400;
        }

        .right-info {
            position: absolute;
            top: 45mm;
            right: 27mm;
            width: 72mm;
            text-align: right;
            font-size: 10pt;
            line-height: 1.25;
            color: #111111;
            font-weight: 400;
        }

        .offer-number {
            font-size: 12pt;
            font-weight: 900;
            line-height: 1.25;
            margin-bottom: 2mm;
        }

        .offer-date {
            font-size: 12pt;
            font-weight: 700;
            margin-bottom: 7mm;
        }

        .recipient {
            font-size: 10pt;
            line-height: 1.22;
            text-align: right;
            font-weight: 400;
        }

        .recipient strong {
            font-weight: 700;
        }

        .main-title {
            position: absolute;
            top: 96mm;
            left: 0;
            width: 210mm;
            text-align: center;
            font-size: 20pt;
            font-weight: 900;
            letter-spacing: .2px;
            text-transform: uppercase;
        }

        .content-area {
            position: absolute;
            top: 112mm;
            left: 25mm;
            width: 160mm;
        }

        .content-area.continuation {
            top: 36mm;
        }
        .content-area.summary-only {
            top: 38mm;
        }

        .items-table {
            width: 160mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .items-table th {
            text-transform: uppercase;
            font-size: 10pt;
            font-weight: 900;
            text-align: left;
            padding: 0 0 5mm 0;
            border-bottom: 1.3px solid #555555;
        }

        .items-table td {
            font-size: 9pt;
            padding: 3.1mm 0 0 0;
            vertical-align: top;
            font-weight: 400;
        }

        .items-table th:nth-child(1),
        .items-table td:nth-child(1) {
            width: 82mm;
            text-align: left;
        }

        .items-table th:nth-child(2),
        .items-table td:nth-child(2) {
            width: 26mm;
            text-align: center;
        }

        .items-table th:nth-child(3),
        .items-table td:nth-child(3) {
            width: 26mm;
            text-align: right;
        }

        .items-table th:nth-child(4),
        .items-table td:nth-child(4) {
            width: 26mm;
            text-align: right;
        }

        .empty-product-row td {
            height: 7mm;
            padding-top: 3.1mm;
            font-size: 9pt;
            font-weight: 400;
        }

        .content-area.summary-only .after-table-block,
        .no-logo-table-area.summary-only
        .after-table-block {
            margin-top: 6mm;
            width: 160mm;
            page-break-inside: avoid;
        }

        .after-table-block {
            margin-top: 6mm;
            width: 160mm;
            page-break-inside: avoid;
        }
        .after-table-line {
            width: 70mm;
            border-top: 1.3px solid #777777;
            margin-left: auto;
            margin-bottom: 1.8mm;
        }
        .after-table-content {
            display: table;
            width: 160mm;
            page-break-inside: avoid;
        }
        .after-table-left {
            display: table-cell;
            width: 75mm;
            vertical-align: top;
            padding-top: 36mm;
        }
        .after-table-right {
            display: table-cell;
            width: 85mm;
            vertical-align: top;
            text-align: right;
        }
        .thank-you {
            font-size: 12pt;
            font-weight: 900;
            color: #111111;
            margin-bottom: 7mm;
        }
        .bottom-company {
            font-size: 12pt;
            font-weight: 400;
            color: #111111;
        }
        .summary-table {
            width: 70mm;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 12pt;
            line-height: 1.15;
        }
        .summary-table td {
            padding: 0.65mm 0;
            font-size: 12pt;
        }
        .summary-table td:first-child {
            width: 43mm;
            text-align: left;
            font-weight: 700;
            padding-right: 6mm;
        }
        .summary-table td:last-child {
            width: 27mm;
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
        }
        .summary-table .tax-row td {
            font-size: 12pt;
            font-weight: 400 !important;
        }
        .summary-total td {
            font-size: 12pt;
            font-weight: 900;
        }

        .no-logo-title {
            position: absolute;
            top: 34mm;
            left: 0;
            width: 210mm;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .no-logo-meta {
            position: absolute;
            top: 53mm;
            left: 0;
            width: 210mm;
            text-align: center;
            font-size: 12px;
            line-height: 1.55;
        }

        .no-logo-company {
            position: absolute;
            top: 80mm;
            left: 36mm;
            width: 80mm;
            font-size: 12px;
            line-height: 1.45;
        }

        .no-logo-table-area {
            position: absolute;
            top: 116mm;
            left: 25mm;
            width: 160mm;
        }

        .no-logo-table-area.continuation {
            top: 36mm;
        }
        .no-logo-table-area.summary-only {
            top: 38mm;
        }

        .no-logo-payment {
            font-size: 11px;
        }
    
        /* PDF_HARD_BACKGROUND_STYLE_START */
        .pdf-hard-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            object-fit: cover;
            z-index: 0;
        }

        .page {
            position: relative;
        }

        .page > *:not(.pdf-hard-background) {
            position: relative;
            z-index: 1;
        }
        /* PDF_HARD_BACKGROUND_STYLE_END */

    </style>
</head>

@php
    $isLogoTemplate = (bool) ($template->show_logo ?? false);

    $documentTitle = $documentType === 'invoice'
        ? ($isLogoTemplate ? 'RECHNUNG' : 'INVOICE')
        : ($isLogoTemplate ? 'PREISANGEBOT' : 'PRICE OFFER');

    $numberLabel = $documentType === 'invoice'
        ? ($isLogoTemplate ? 'Rechnung Nr.:' : 'INVOICE NR.:')
        : ($isLogoTemplate ? 'Preisangebot Nr.:' : 'OFFER NR.:');

    $companyAddressLines = [];

    if ($template->company_street || $template->company_house_number) {
        $companyAddressLines[] = trim(($template->company_street ?? '') . ' ' . ($template->company_house_number ?? ''));
    }

    if ($template->company_postal_code || $template->company_city) {
        $companyAddressLines[] = trim(($template->company_postal_code ?? '') . ' ' . ($template->company_city ?? ''));
    }

    if ($template->company_country) {
        $companyAddressLines[] = $template->company_country;
    }

    if (empty($companyAddressLines) && $template->company_address) {
        $companyAddressLines = explode("\n", $template->company_address);
    }

    $customerAddressLines = [];

    if ($offer->customer?->billing_street || $offer->customer?->billing_house_number) {
        $customerAddressLines[] = trim(($offer->customer->billing_street ?? '') . ' ' . ($offer->customer->billing_house_number ?? ''));
    }

    if ($offer->customer?->billing_postal_code || $offer->customer?->billing_city) {
        $customerAddressLines[] = trim(($offer->customer->billing_postal_code ?? '') . ' ' . ($offer->customer->billing_city ?? ''));
    }

    if ($offer->customer?->billing_country) {
        $customerAddressLines[] = $offer->customer->billing_country;
    }

    if (empty($customerAddressLines) && $offer->customer?->billing_address) {
        $customerAddressLines = explode("\n", $offer->customer->billing_address);
    }

    $validUntil = $offer->reserved_until ?? $offer->valid_until ?? $offer->expires_at ?? null;

    try {
        $validUntilText = $validUntil
            ? \Carbon\Carbon::parse($validUntil)->format($isLogoTemplate ? 'd.m.Y - H:i \\U\\h\\r' : 'd /m /Y - H:i')
            : null;
    } catch (\Throwable $e) {
        $validUntilText = null;
    }

    $taxRate = (float) ($template->tax_rate ?? 19);
    $taxRateLabel = rtrim(rtrim(number_format($taxRate, 2, ',', '.'), '0'), ',');
    $subtotal = (float) $offer->items->sum('line_total');
    $taxAmount = round($subtotal * ($taxRate / 100), 2);
    $grandTotal = round($subtotal + $taxAmount, 2);

    $productLabel = $isLogoTemplate
        ? ($template->product_column_label ?: 'Beschreibung')
        : 'Product';

    $quantityLabel = $isLogoTemplate ? 'Menge' : 'Quantity';
    $priceLabel = $isLogoTemplate ? 'Preis' : 'Price';
    $sumLabel = $isLogoTemplate ? 'Summe' : 'Sum';

    $subtotalLabel = $isLogoTemplate ? 'Zwischensumme' : 'NET:';
    $taxLabel = $isLogoTemplate ? 'MwSt. (' . $taxRateLabel . '%)' : 'VAT (' . $taxRateLabel . ' %):';
    $totalLabel = $isLogoTemplate ? 'Total' : 'TOTAL:';

    $items = $offer->items->values();

    /*
     * Finale PDF-Seitenlogik:
     *
     * 1–8 Produkte:
     * - Seite 1 enthält Produkte + Summe/Dank.
     *
     * Ab 9 Produkten:
     * - Seite 1 enthält max. 15 Produkte, ohne Summe/Dank.
     * - Folgeseiten ohne Summe enthalten max. 24 Produkte.
     * - Letzte Seite mit Summe/Dank enthält max. 14 Produkte + Summe/Dank.
     *
     * Beispiele:
     * - 8 Produkte: Seite 1 = 8 + Summe/Dank
     * - 9 Produkte: Seite 1 = 9, Seite 2 = Summe/Dank
     * - 15 Produkte: Seite 1 = 15, Seite 2 = Summe/Dank
     * - 24 Produkte: Seite 1 = 15, Seite 2 = 9 + Summe/Dank
     * - 30 Produkte: Seite 1 = 15, Seite 2 = 15, Seite 3 = Summe/Dank
     * - 39 Produkte: Seite 1 = 15, Seite 2 = 24, Seite 3 = Summe/Dank
     * - 41 Produkte: Seite 1 = 15, Seite 2 = 24, Seite 3 = 2 + Summe/Dank
     */
    $firstPageWithSummaryLimit = 8;
    $firstPageProductOnlyLimit = 15;

    $normalPageProductOnlyLimit = 24;
    $normalPageWithSummaryLimit = 14;

    $chunks = collect();
    $totalItems = $items->count();

    if ($totalItems <= $firstPageWithSummaryLimit) {
        // Bis 8 Produkte: alles auf Seite 1 inkl. Summe/Dank.
        $chunks->push($items);
    } else {
        // Ab 9 Produkten: erste Seite nur Produkte, max. 15.
        $chunks->push($items->slice(0, $firstPageProductOnlyLimit)->values());

        $remaining = $items->slice($firstPageProductOnlyLimit)->values();

        if ($remaining->count() === 0) {
            // 9–15 Produkte: Summe/Dank bekommt eine eigene zweite Seite.
            $chunks->push(collect());
        } else {
            while ($remaining->count() > 0) {
                $remainingCount = $remaining->count();

                if ($remainingCount <= $normalPageWithSummaryLimit) {
                    // Rest passt zusammen mit Summe/Dank auf die letzte Seite.
                    $chunks->push($remaining->values());
                    $remaining = collect();
                    break;
                }

                if ($remainingCount <= $normalPageProductOnlyLimit) {
                    // Rest passt als Produktseite, aber nicht mehr mit Summe/Dank.
                    // Deshalb danach eigene Summen-/Dank-Seite.
                    $chunks->push($remaining->values());
                    $chunks->push(collect());
                    $remaining = collect();
                    break;
                }

                // Volle Folgeseite mit max. 24 Produkten.
                $chunks->push($remaining->slice(0, $normalPageProductOnlyLimit)->values());
                $remaining = $remaining->slice($normalPageProductOnlyLimit)->values();
            }
        }
    }


    // BILLING_ADDRESS_PDF_START
    $billingAddressLines = [];

    if ($offer->customer) {
        $customer = $offer->customer;

        $billingStreet = trim((string) ($customer->billing_street ?? ''));
        $billingHouseNumber = trim((string) ($customer->billing_house_number ?? ''));
        $billingPostalCode = trim((string) ($customer->billing_postal_code ?? ''));
        $billingCity = trim((string) ($customer->billing_city ?? ''));
        $billingCountry = trim((string) ($customer->billing_country ?? ''));

        $streetLine = trim($billingStreet . ' ' . $billingHouseNumber);
        if ($streetLine !== '') {
            $billingAddressLines[] = $streetLine;
        }

        $cityLine = trim($billingPostalCode . ' ' . $billingCity);
        if ($cityLine !== '') {
            $billingAddressLines[] = $cityLine;
        }

        if ($billingCountry !== '') {
            $billingAddressLines[] = $billingCountry;
        }

        if (empty($billingAddressLines) && ! empty($customer->billing_address)) {
            $billingAddressLines = array_values(array_filter(
                preg_split('/\r\n|\r|\n/', trim((string) $customer->billing_address)),
                fn ($line) => trim((string) $line) !== ''
            ));
        }

        if (! empty($billingAddressLines)) {
            $customerAddressLines = $billingAddressLines;
        }
    }
    // BILLING_ADDRESS_PDF_END

@endphp

<body class="{{ $isLogoTemplate ? 'font-logo' : 'font-no-logo' }}">
@foreach ($chunks as $pageIndex => $pageItems)
    @php
        $isFirstPage = $pageIndex === 0;
        $isLastPage = $pageIndex === $chunks->count() - 1;
    @endphp

    <div
        class="page {{ $isLastPage ? 'last-page' : '' }}"
        @if (! empty($backgroundDataUri))
            style="background-image:url('{{ $backgroundDataUri }}'); background-repeat:no-repeat; background-position:top left; background-size:210mm 297mm;"
        @endif
    >
        @if ($isLogoTemplate)
            @if ($isFirstPage)
                <div class="company-block">
                    <div class="logo-wrap">
                        @if ($logoDataUri)
                            <img src="{{ $logoDataUri }}" class="logo">
                        @elseif ($template->company_name)
                            <div>{{ $template->company_name }}</div>
                        @endif
                    </div>

                    @if ($template->show_company_details)
                        <div class="company-details">
                            @foreach ($companyAddressLines as $line)
                                {{ $line }}<br>
                            @endforeach

                            @if ($template->company_vat_id ?? false)
                                USt-ID: {{ $template->company_vat_id }}<br>
                            @endif

                            @if ($template->company_phone)
                                {{ $template->company_phone }}<br>
                            @endif

                            @if ($template->company_email)
                                {{ $template->company_email }}<br>
                            @endif

                            @if ($template->company_website ?? false)
                                {{ $template->company_website }}
                            @endif
                        </div>
                    @endif
                </div>

                <div class="right-info">
                    <div class="offer-number">{{ $numberLabel }} {{ $offer->offer_number }}</div>
                    <div class="offer-date">{{ $validUntilText ? 'Gültig bis: ' . $validUntilText : now()->format('d.m.Y') }}</div>

                    <div class="recipient">
                        <strong>{{ $offer->customer?->company_name }}</strong><br>
                        @foreach ($customerAddressLines as $line)
                            {{ $line }}<br>
                        @endforeach
                    </div>
                </div>

                <div class="main-title">{{ $documentTitle }}</div>
            @endif

            <div class="content-area {{ $pageItems->count() === 0 ? 'summary-only' : ($isFirstPage ? '' : 'continuation') }}">
                @if ($pageItems->count() > 0)
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>{{ $productLabel }}</th>
                                <th>{{ $quantityLabel }}</th>
                                <th>{{ $priceLabel }}</th>
                                <th>{{ $sumLabel }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pageItems as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 2, ',', '.') }}€</td>
                                    <td>{{ number_format((float) $item->line_total, 2, ',', '.') }}€</td>
                                </tr>
                            @endforeach

                            @if ($isLastPage)
                                <tr class="empty-product-row">
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif

                @if ($isLastPage)
                    <div class="after-table-block">
                        <div class="after-table-line"></div>

                        <div class="after-table-content">
                            <div class="after-table-left">
                                <div class="thank-you">Vielen Dank für Ihr Vertrauen</div>

                                @if ($template->company_name)
                                    <div class="bottom-company">{{ $template->company_name }}</div>
                                @endif
                            </div>

                            <div class="after-table-right">
                                <table class="summary-table">
                                    <tr>
                                        <td>{{ $subtotalLabel }}</td>
                                        <td>{{ number_format($subtotal, 2, ',', '.') }}€</td>
                                    </tr>
                                    <tr class="tax-row">
                                        <td>{{ $taxLabel }}</td>
                                        <td>{{ number_format($taxAmount, 2, ',', '.') }}€</td>
                                    </tr>
                                    <tr class="summary-total">
                                        <td>{{ $totalLabel }}</td>
                                        <td>{{ number_format($grandTotal, 2, ',', '.') }}€</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            @if ($isFirstPage)
                <div class="no-logo-title">{{ $documentTitle }}</div>

                <div class="no-logo-meta">
                    <strong>{{ $numberLabel }}</strong> {{ $offer->offer_number }}<br>
                    @if ($validUntilText)
                        Valid until: {{ $validUntilText }}
                    @else
                        {{ now()->format('d /m /Y - H:i') }}
                    @endif
                </div>

                <div class="no-logo-company">
                    @if ($template->company_name)
                        <strong>{{ $template->company_name }}</strong><br>
                    @endif

                    @foreach ($companyAddressLines as $line)
                        {{ $line }}<br>
                    @endforeach
                </div>
            @endif

            <div class="no-logo-table-area {{ $pageItems->count() === 0 ? 'summary-only' : ($isFirstPage ? '' : 'continuation') }}">
                @if ($pageItems->count() > 0)
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>{{ $productLabel }}</th>
                                <th>{{ $quantityLabel }}</th>
                                <th>{{ $priceLabel }}</th>
                                <th>{{ $sumLabel }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($pageItems as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 2, ',', '.') }}€</td>
                                    <td>{{ number_format((float) $item->line_total, 2, ',', '.') }}€</td>
                                </tr>
                            @endforeach

                            @if ($isLastPage)
                                <tr class="empty-product-row">
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                @endif

                @if ($isLastPage)
                    <div class="after-table-block">
                        <div class="after-table-line"></div>

                        <div class="after-table-content">
                            <div class="after-table-left">
                                <div class="no-logo-payment">
                                    Please pay the full amount before the offer expires.
                                </div>
                            </div>

                            <div class="after-table-right">
                                <table class="summary-table">
                                    <tr>
                                        <td>{{ $subtotalLabel }}</td>
                                        <td>{{ number_format($subtotal, 2, ',', '.') }}€</td>
                                    </tr>
                                    <tr class="tax-row">
                                        <td>{{ $taxLabel }}</td>
                                        <td>{{ number_format($taxAmount, 2, ',', '.') }}€</td>
                                    </tr>
                                    <tr class="summary-total">
                                        <td>{{ $totalLabel }}</td>
                                        <td>{{ number_format($grandTotal, 2, ',', '.') }}€</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endforeach
</body>
</html>
