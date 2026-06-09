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

        * {
            box-sizing: border-box;
        }

        body,
        body * {
            font-family: 'GlacialPDF', DejaVu Sans, sans-serif !important;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f4f0ed;
            color: #111;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
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
            letter-spacing: .4px;
            line-height: 1;
            text-transform: uppercase;
        }

        .meta {
            position: absolute;
            top: 51mm;
            left: 21mm;
            font-size: 10.5pt;
            line-height: 1.15;
        }

        .meta strong {
            font-weight: 700;
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
            font-size: 9pt;
            font-weight: 700;
            letter-spacing: .8px;
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
            padding-top: 3.25mm;
            line-height: 1;
            vertical-align: top;
        }

        table.items th:nth-child(1),
        table.items td:nth-child(1) {
            width: 125mm;
            text-align: left;
            padding-left: 4mm;
        }

        table.items th:nth-child(2),
        table.items td:nth-child(2) {
            width: 49mm;
            text-align: right;
            padding-right: 4mm;
        }

        .note {
            margin-top: 14mm;
            font-size: 10pt;
            line-height: 1.4;
        }
    </style>
</head>
<body>
@php
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
        'items' => $remaining->take(15),
        'first' => true,
    ];

    $remaining = $remaining->slice(15)->values();

    while ($remaining->count() > 0) {
        $chunks[] = [
            'items' => $remaining->take(22),
            'first' => false,
        ];

        $remaining = $remaining->slice(22)->values();
    }
@endphp

@foreach ($chunks as $chunk)
    <div class="page">
        @if ($chunk['first'])
            <div class="title">DELIVERY NOTE</div>

            <div class="meta">
                DELIVERY NOTE NR.: <strong>{{ $offer->offer_number }}</strong><br>
                <strong>Date: {{ now()->format('d /m /Y') }}</strong>
            </div>

            <div class="line"></div>

            <div class="customer">
                @if ($offer->customer?->company_name)
                    {{ $offer->customer->company_name }}<br>
                @endif

                @foreach ($deliveryAddressLines as $line)
                    {{ $line }}<br>
                @endforeach
            </div>
        @endif

        <div class="table-wrap {{ $chunk['first'] ? '' : 'continuation' }}">
            <table class="items">
                <thead>
                    <tr>
                        <th>PRODUCT</th>
                        <th>QUANTITY</th>
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
                    Goods received: _______________________________<br><br>
                    Date / Signature
                </div>
            @endif
        </div>
    </div>
@endforeach
</body>
</html>
