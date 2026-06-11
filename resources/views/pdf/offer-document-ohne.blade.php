<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>

        @font-face {
            font-family: 'GlacialPDF';
            font-style: normal;
            font-weight: 400;
            src: url("file://{{ public_path('fonts/glacial/GlacialIndifference-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'GlacialPDF';
            font-style: normal;
            font-weight: 700;
            src: url("file://{{ public_path('fonts/glacial/GlacialIndifference-Bold.ttf') }}") format("truetype");
        }

        @page {
            margin: 0;
            size: A4 portrait;
        }
}") format("opentype");
        }
}") format("opentype");
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f4f0ed;
            color: #111;
            font-family: 'GlacialPDF', DejaVu Sans, sans-serif;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            padding: 0;
            background: #f4f0ed;
            page-break-after: always;
            overflow: hidden;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .title {
            position: absolute;
            top: 26mm;
            left: 21mm;
            font-size: 31pt;
            font-weight: 400;
            letter-spacing: 0.4px;
            line-height: 1;
        }

        .meta {
            position: absolute;
            top: 51mm;
            left: 21mm;
            font-size: 10.5pt;
            line-height: 1.15;
        }

        .meta strong {
            font-weight: 400;
        }

        .line {
            position: absolute;
            top: 69mm;
            left: 21mm;
            width: 82mm;
            border-top: 1.2px solid #766765;
        }

        .customer {
            position: absolute;
            top: 80mm;
            left: 21mm;
            width: 95mm;
            font-size: 9.8pt;
            line-height: 1.25;
        }

        .table-wrap {
            position: absolute;
            top: 116mm;
            left: 18mm;
            width: 174mm;
        }

        .table-wrap.continuation {
            top: 32mm;
        }

        table.items {
            width: 174mm;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
        }

        table.items th {
            background: #d2cbc7;
            font-size: 8.8pt;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            padding: 2.45mm 0;
            line-height: 1;
        }

        table.items th:first-child {
            border-radius: 4.5mm 0 0 4.5mm;
            padding-left: 4mm;
        }

        table.items th:last-child {
            border-radius: 0 4.5mm 4.5mm 0;
            padding-right: 4mm;
        }

        table.items td {
            font-size: 8.8pt;
            font-weight: 400;
            padding-top: 3.25mm;
            line-height: 1;
            vertical-align: top;
        }

        table.items th:nth-child(1),
        table.items td:nth-child(1) {
            width: 43mm;
            text-align: left;
            padding-left: 4mm;
        }

        table.items th:nth-child(2),
        table.items td:nth-child(2) {
            width: 39mm;
            text-align: left;
        }

        table.items th:nth-child(3),
        table.items td:nth-child(3) {
            width: 42mm;
            text-align: left;
        }

        table.items th:nth-child(4),
        table.items td:nth-child(4) {
            width: 50mm;
            text-align: right;
            padding-right: 4mm;
        }

        .bottom {
            width: 174mm;
            margin-top: 15mm;
            display: table;
            page-break-inside: avoid;
        }

        .bottom-left {
            display: table-cell;
            width: 92mm;
            vertical-align: top;
            padding-top: 36mm;
            font-size: 10.5pt;
            line-height: 1.15;
        }

        .bottom-right {
            display: table-cell;
            width: 82mm;
            vertical-align: top;
            text-align: right;
        }

        .summary-line {
            width: 70mm;
            border-top: 1.2px solid #766765;
            margin-left: auto;
            margin-bottom: 3mm;
        }

        table.summary {
            width: 70mm;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 8.8pt;
        }

        table.summary td {
            padding: 1mm 0;
        }

        table.summary td:first-child {
            width: 32mm;
            text-align: left;
            font-weight: 700;
        }

        table.summary td:last-child {
            width: 38mm;
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
        }
    
        body,
        body * {
            font-family: 'GlacialPDF', DejaVu Sans, sans-serif !important;
        }

        body.glacial-force {
            font-family: 'GlacialPDF', DejaVu Sans, sans-serif !important;
        }
    
        /* PDF_BACKGROUND_SUPPORT_START */
        .pdf-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            object-fit: cover;
            z-index: 0;
        }

        .page > :not(.pdf-background) {
            position: relative;
            z-index: 1;
        }
        /* PDF_BACKGROUND_SUPPORT_END */

    
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

<body class="glacial-force">
@php
    $documentType = $documentType ?? 'offer';

    $title = 'PRICE OFFER';
    $numberLabel = $documentType === 'invoice' ? 'INVOICE NR.:' : 'OFFER NR.:';

    $validUntilText = null;
    if (!empty($offer->valid_until)) {
        $validUntilText = \Carbon\Carbon::parse($offer->valid_until)->format('d /m /Y - H:i');
    }

    $customerAddressLines = [];
    if ($offer->customer) {
        if ($offer->customer->street ?? false) {
            $customerAddressLines[] = trim(($offer->customer->street ?? '') . ' ' . ($offer->customer->house_number ?? ''));
        }

        $cityLine = trim(($offer->customer->postal_code ?? '') . ' ' . ($offer->customer->city ?? ''));
        if ($cityLine !== '') {
            $customerAddressLines[] = $cityLine;
        }

        if ($offer->customer->country ?? false) {
            $customerAddressLines[] = $offer->customer->country;
        }
    }

    $items = $offer->items->values();

    $subtotal = (float) $offer->items->sum('line_total');
    $grandTotal = round($subtotal, 2);

    /*
      Pagination:
      Erste Seite: Kopf + max 15 Produkte.
      Folgeseiten: max 24 Produkte.
      Letzte Seite mit Summe: max 15 Produkte + Bottom.
    */
    $chunks = [];
    $remaining = $items;

    $firstPageItems = $remaining->take(15);
    $chunks[] = [
        'items' => $firstPageItems,
        'first' => true,
        'summary' => false,
    ];
    $remaining = $remaining->slice(15)->values();

    while ($remaining->count() > 15) {
        $chunks[] = [
            'items' => $remaining->take(24),
            'first' => false,
            'summary' => false,
        ];
        $remaining = $remaining->slice(24)->values();
    }

    $chunks[] = [
        'items' => $remaining,
        'first' => false,
        'summary' => true,
    ];


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

@foreach ($chunks as $chunk)
    <div class="page">
        {{-- PDF_HARD_BACKGROUND_IMAGE_START --}}
        @if (! empty($backgroundDataUri))
            <img class="pdf-hard-background" src="{{ $backgroundDataUri }}" alt="">
        @endif
        {{-- PDF_HARD_BACKGROUND_IMAGE_END --}}
        {{-- PDF_BACKGROUND_IMAGE_START --}}
        @if (! empty($backgroundDataUri))
            <img class="pdf-background" src="{{ $backgroundDataUri }}" alt="">
        @endif
        {{-- PDF_BACKGROUND_IMAGE_END --}}
        @if ($chunk['first'])
            <div class="title">{{ $title }}</div>

            <div class="meta">
                {{ $numberLabel }} <strong>{{ $offer->offer_number }}</strong><br>
                <strong>
                    @if ($validUntilText)
                        Valid until: {{ $validUntilText }}
                    @else
                        Valid until: {{ now()->format('d /m /Y - H:i') }}
                    @endif
                </strong>
            </div>

            <div class="line"></div>

            <div class="customer">
                @if ($offer->customer?->company_name)
                    {{ $offer->customer->company_name }}<br>
                @endif

                @foreach ($customerAddressLines as $line)
                    {{ $line }}<br>
                @endforeach
            </div>
        @endif

        <div class="table-wrap {{ $chunk['first'] ? '' : 'continuation' }}">
            @if ($chunk['items']->count())
                <table class="items">
                    <thead>
                        <tr>
                            <th>PRODUCT</th>
                            <th>QUANTITY</th>
                            <th>PRICE</th>
                            <th>SUM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chunk['items'] as $item)
                            <tr>
                                <td>{{ $item->product_name ?? $item->description ?? '' }}</td>
                                <td>{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                                <td>{{ number_format((float) $item->unit_price, 2, ',', '.') }}€</td>
                                <td>{{ number_format((float) $item->line_total, 2, ',', '.') }}€</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($chunk['summary'])
                <div class="bottom">
                    <div class="bottom-left">
                        Please pay the full amount before<br>
                        the offer expires.
                    </div>

                    <div class="bottom-right">
                        <div class="summary-line"></div>

                        <table class="summary">
                            <tr>
                                <td>TOTAL:</td>
                                <td>{{ number_format($grandTotal, 2, ',', '.') }}€</td>
                            </tr>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endforeach
</body>
</html>
