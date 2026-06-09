<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }

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

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'ExoPDF', DejaVu Sans, sans-serif;
            color: #1f2937;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            padding: 0;
            page-break-after: always;
            overflow: hidden;
            background: #ffffff;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .background {
            position: absolute;
            inset: 0;
            width: 210mm;
            height: 297mm;
            object-fit: cover;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .logo {
            position: absolute;
            top: 24mm;
            left: 18mm;
            width: 38mm;
            max-height: 30mm;
        }

        .company {
            position: absolute;
            top: 42mm;
            left: 18mm;
            width: 72mm;
            font-size: 9pt;
            line-height: 1.35;
        }

        .company.with-logo {
            top: 42mm;
        }

        .right-info {
            position: absolute;
            top: 34mm;
            right: 18mm;
            width: 82mm;
            text-align: right;
            font-size: 10pt;
            line-height: 1.35;
        }

        .recipient {
            margin-top: 10mm;
            font-size: 10pt;
            line-height: 1.35;
        }

        .title {
            position: absolute;
            top: 103mm;
            left: 18mm;
            right: 18mm;
            text-align: center;
            font-size: 27pt;
            font-weight: 900;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .table-wrap {
            position: absolute;
            top: 128mm;
            left: 18mm;
            width: 174mm;
        }

        .table-wrap.continuation {
            top: 34mm;
        }

        table.items {
            width: 174mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.items th {
            font-size: 10pt;
            font-weight: 900;
            text-transform: uppercase;
            border-bottom: 1px solid #111;
            padding: 2.2mm 0;
        }

        table.items td {
            font-size: 10pt;
            padding-top: 3.2mm;
            vertical-align: top;
        }

        table.items th:nth-child(1),
        table.items td:nth-child(1) {
            width: 125mm;
            text-align: left;
        }

        table.items th:nth-child(2),
        table.items td:nth-child(2) {
            width: 49mm;
            text-align: right;
        }

        .note {
            margin-top: 12mm;
            font-size: 10pt;
            line-height: 1.4;
        }
    </style>
</head>
<body>
@php
    $companyAddressLines = [];
    foreach ([
        trim(($template->company_street ?? '') . ' ' . ($template->company_house_number ?? '')),
        trim(($template->company_postal_code ?? '') . ' ' . ($template->company_city ?? '')),
        $template->company_country ?? null,
    ] as $line) {
        if (trim((string) $line) !== '') {
            $companyAddressLines[] = trim((string) $line);
        }
    }

    $deliveryAddressLines = [];
    if ($offer->customer) {
        $customer = $offer->customer;

        // Lieferschein nutzt zuerst die Lieferadresse.
        $deliveryStreet = trim((string) ($customer->delivery_street ?? ''));
        $deliveryHouseNumber = trim((string) ($customer->delivery_house_number ?? ''));
        $deliveryPostalCode = trim((string) ($customer->delivery_postal_code ?? ''));
        $deliveryCity = trim((string) ($customer->delivery_city ?? ''));
        $deliveryCountry = trim((string) ($customer->delivery_country ?? ''));

        $streetLine = trim($deliveryStreet . ' ' . $deliveryHouseNumber);
        if ($streetLine !== '') {
            $deliveryAddressLines[] = $streetLine;
        }

        $cityLine = trim($deliveryPostalCode . ' ' . $deliveryCity);
        if ($cityLine !== '') {
            $deliveryAddressLines[] = $cityLine;
        }

        if ($deliveryCountry !== '') {
            $deliveryAddressLines[] = $deliveryCountry;
        }

        // Fallback 1: altes Lieferadresse-Textfeld
        if (empty($deliveryAddressLines) && ! empty($customer->delivery_address)) {
            $deliveryAddressLines = array_values(array_filter(
                preg_split('/
||
/', trim((string) $customer->delivery_address)),
                fn ($line) => trim((string) $line) !== ''
            ));
        }

        // Fallback 2: Rechnungsadresse
        if (empty($deliveryAddressLines)) {
            $billingStreet = trim((string) ($customer->billing_street ?? ''));
            $billingHouseNumber = trim((string) ($customer->billing_house_number ?? ''));
            $billingPostalCode = trim((string) ($customer->billing_postal_code ?? ''));
            $billingCity = trim((string) ($customer->billing_city ?? ''));
            $billingCountry = trim((string) ($customer->billing_country ?? ''));

            $streetLine = trim($billingStreet . ' ' . $billingHouseNumber);
            if ($streetLine !== '') {
                $deliveryAddressLines[] = $streetLine;
            }

            $cityLine = trim($billingPostalCode . ' ' . $billingCity);
            if ($cityLine !== '') {
                $deliveryAddressLines[] = $cityLine;
            }

            if ($billingCountry !== '') {
                $deliveryAddressLines[] = $billingCountry;
            }
        }

        // Fallback 3: altes Rechnungsadresse-Textfeld
        if (empty($deliveryAddressLines) && ! empty($customer->billing_address)) {
            $deliveryAddressLines = array_values(array_filter(
                preg_split('/
||
/', trim((string) $customer->billing_address)),
                fn ($line) => trim((string) $line) !== ''
            ));
        }

        // Fallback 4: Stadt
        if (empty($deliveryAddressLines) && ! empty($customer->city)) {
            $deliveryAddressLines[] = $customer->city;
        }
    }

    $items = $offer->items->values();

    $chunks = [];
    $remaining = $items;

    $chunks[] = [
        'items' => $remaining->take(12),
        'first' => true,
    ];

    $remaining = $remaining->slice(12)->values();

    while ($remaining->count() > 0) {
        $chunks[] = [
            'items' => $remaining->take(20),
            'first' => false,
        ];

        $remaining = $remaining->slice(20)->values();
    }
@endphp

@foreach ($chunks as $chunk)
    <div class="page">
        @if ($backgroundDataUri)
            <img class="background" src="{{ $backgroundDataUri }}" alt="">
        @endif

        <div class="content">
            @if ($chunk['first'])
                @if ($logoDataUri)
                    <img class="logo" src="{{ $logoDataUri }}" alt="">
                @endif

                <div class="company {{ $logoDataUri ? 'with-logo' : '' }}">
                    @if ($template->company_name)
                        <strong>{{ $template->company_name }}</strong><br>
                    @endif

                    @foreach ($companyAddressLines as $line)
                        {{ $line }}<br>
                    @endforeach

                    @if ($template->company_vat_id)
                        USt-ID: {{ $template->company_vat_id }}<br>
                    @endif

                    @if ($template->company_phone)
                        {{ $template->company_phone }}<br>
                    @endif

                    @if ($template->company_email)
                        {{ $template->company_email }}<br>
                    @endif

                    @if ($template->company_website)
                        {{ $template->company_website }}<br>
                    @endif
                </div>

                <div class="right-info">
                    <strong>Lieferschein Nr.: {{ $offer->offer_number }}</strong><br>
                    Datum: {{ now()->format('d.m.Y') }}

                    <div class="recipient">
                        @if ($offer->customer?->company_name)
                            <strong>{{ $offer->customer->company_name }}</strong><br>
                        @endif

                        @foreach ($deliveryAddressLines as $line)
                            {{ $line }}<br>
                        @endforeach
                    </div>
                </div>

                <div class="title">Lieferschein</div>
            @endif

            <div class="table-wrap {{ $chunk['first'] ? '' : 'continuation' }}">
                <table class="items">
                    <thead>
                        <tr>
                            <th>Beschreibung</th>
                            <th>Menge</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chunk['items'] as $item)
                            <tr>
                                <td>{{ $item->product_name ?? $item->description ?? $item->product?->name ?? '' }}</td>
                                <td>{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($loop->last)
                    <div class="note">
                        Ware erhalten: _______________________________<br><br>
                        Datum / Unterschrift
                    </div>
                @endif
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
