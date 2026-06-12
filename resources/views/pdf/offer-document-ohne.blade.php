<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; size: A4 portrait; }

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

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: 'GlacialPDF', DejaVu Sans, sans-serif;
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

        .pdf-background {
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
            padding: 22mm 20mm 18mm;
        }

        .title {
            font-size: 24pt;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0 0 8mm;
        }

        .meta {
            font-size: 10.5pt;
            line-height: 1.4;
            margin-bottom: 8mm;
        }

        .customer {
            font-size: 10.5pt;
            line-height: 1.35;
            margin-bottom: 10mm;
        }

        table.items {
            width: 170mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.items th {
            background: #d2cbc7;
            font-size: 9.4pt;
            font-weight: 700;
            padding: 2.35mm 2mm;
            border-bottom: 1px solid #999;
            text-align: left;
            line-height: 1;
        }

        table.items td {
            font-size: 9.4pt;
            padding: 1.85mm 2mm;
            border-bottom: 1px solid #c8c0bc;
            vertical-align: top;
            line-height: 1.12;
        }

        table.items th:nth-child(1),
        table.items td:nth-child(1) {
            width: 70mm;
        }

        table.items th:nth-child(2),
        table.items td:nth-child(2) {
            width: 30mm;
            text-align: right;
        }

        table.items th:nth-child(3),
        table.items td:nth-child(3) {
            width: 34mm;
            text-align: right;
        }

        table.items th:nth-child(4),
        table.items td:nth-child(4) {
            width: 36mm;
            text-align: right;
        }

        .summary {
            position: absolute;
            right: 20mm;
            bottom: 28mm;
            width: 72mm;
            font-size: 11pt;
            background: rgba(244, 240, 237, .94);
            padding-top: 3mm;
        }

        .summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            padding: 2mm 0;
            border-top: 1px solid #999;
        }

        .summary td:last-child {
            text-align: right;
            font-weight: 700;
        }

        .note {
            position: absolute;
            left: 20mm;
            bottom: 28mm;
            width: 82mm;
            font-size: 9.8pt;
            line-height: 1.35;
            background: rgba(244, 240, 237, .94);
            padding-top: 3mm;
        }

        .page-number {
            position: absolute;
            right: 20mm;
            bottom: 11mm;
            font-size: 8pt;
            color: #777;
        }
    </style>
</head>
<body>
@php
    $documentTitle = ($documentType ?? null) === 'invoice' ? 'INVOICE' : 'PRICE OFFER';
    $numberLabel = ($documentType ?? null) === 'invoice' ? 'INVOICE NR.:' : 'OFFER NR.:';

    $customerAddressLines = [];
    if ($offer->customer) {
        $customerAddressLines[] = $offer->customer->company_name ?? null;
        $customerAddressLines[] = $offer->customer->contact_person ?? null;

        $streetLine = trim((string) (($offer->customer->billing_street ?? '') . ' ' . ($offer->customer->billing_house_number ?? '')));
        $cityLine = trim((string) (($offer->customer->billing_postal_code ?? '') . ' ' . ($offer->customer->billing_city ?? '')));

        if ($streetLine !== '') $customerAddressLines[] = $streetLine;
        if ($cityLine !== '') $customerAddressLines[] = $cityLine;
        if (! empty($offer->customer->billing_country)) $customerAddressLines[] = $offer->customer->billing_country;

        if (count(array_filter($customerAddressLines)) <= 1 && ! empty($offer->customer->billing_address)) {
            $customerAddressLines = preg_split('/\R/u', trim((string) $offer->customer->billing_address));
        }
    }

    $customerAddressLines = array_values(array_filter($customerAddressLines));

    $shippingGross = (($offer->shipping_method ?? null) === 'Lieferung')
        ? (float) ($offer->shipping_price_gross ?? 0)
        : 0.0;

    $items = $offer->items->values()->map(function ($item) {
        return (object) [
            'description' => $item->product_name ?? $item->description ?? ($item->product?->name ?? ''),
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'line_total' => (float) $item->line_total,
        ];
    });

    if ($shippingGross > 0) {
        $items->push((object) [
            'description' => 'Versand',
            'quantity' => 1,
            'unit_price' => round($shippingGross, 2),
            'line_total' => round($shippingGross, 2),
        ]);
    }

    $grandTotal = round((float) $items->sum('line_total'), 2);

    $firstPageLimit = 12;
    $normalPageLimit = 17;
    $lastPageLimit = 12;

    $chunks = collect();
    $remaining = $items->values();

    $chunks->push($remaining->take($firstPageLimit)->values());
    $remaining = $remaining->slice($firstPageLimit)->values();

    while ($remaining->count() > $lastPageLimit) {
        $chunks->push($remaining->take($normalPageLimit)->values());
        $remaining = $remaining->slice($normalPageLimit)->values();
    }

    if ($remaining->count() > 0) {
        $chunks->push($remaining->values());
    }

    $pageCount = $chunks->count();
@endphp

@foreach ($chunks as $pageIndex => $pageItems)
    <div class="page">
        @if (! empty($backgroundDataUri))
            <img class="pdf-background" src="{{ $backgroundDataUri }}" alt="">
        @endif

        <div class="content">
            @if ($pageIndex === 0)
                <div class="title">{{ $documentTitle }}</div>

                <div class="meta">
                    <strong>{{ $numberLabel }}</strong> {{ $offer->offer_number }}<br>
                    <strong>Valid until:</strong> {{ optional($offer->reserved_until)->format('d /m /Y - H:i') }}
                </div>

                <div class="customer">
                    @foreach ($customerAddressLines as $line)
                        {{ $line }}<br>
                    @endforeach
                </div>
            @else
                <div class="title">{{ $documentTitle }}</div>
                <div class="meta">
                    <strong>{{ $numberLabel }}</strong> {{ $offer->offer_number }}
                </div>
            @endif

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
                    @foreach ($pageItems as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ number_format((float) $item->quantity, 2, ',', '.') }}</td>
                            <td>{{ number_format((float) $item->unit_price, 2, ',', '.') }}€</td>
                            <td>{{ number_format((float) $item->line_total, 2, ',', '.') }}€</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($loop->last)
                <div class="note">
                    Please pay the full amount before<br>
                    the offer expires.
                </div>

                <div class="summary">
                    <table>
                        <tr>
                            <td>TOTAL:</td>
                            <td>{{ number_format($grandTotal, 2, ',', '.') }}€</td>
                        </tr>
                    </table>
                </div>
            @endif

            <div class="page-number">
                Seite {{ $pageIndex + 1 }} / {{ $pageCount }}
            </div>
        </div>
    </div>
@endforeach
</body>
</html>
