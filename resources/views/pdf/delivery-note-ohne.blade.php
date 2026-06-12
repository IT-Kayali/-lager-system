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

        .page:last-child { page-break-after: auto; }

        .meta {
            position: absolute;
            top: 22mm;
            right: 20mm;
            width: 78mm;
            font-size: 10pt;
            line-height: 1.45;
        }

        .meta strong {
            display: inline-block;
            min-width: 29mm;
            font-weight: 700;
        }

        .customer {
            position: absolute;
            top: 38mm;
            left: 21mm;
            width: 95mm;
            font-size: 10pt;
            line-height: 1.35;
        }

        .title {
            position: absolute;
            top: 92mm;
            left: 21mm;
            right: 21mm;
            text-align: center;
            font-size: 22pt;
            font-weight: 700;
        }

        .intro {
            position: absolute;
            top: 112mm;
            left: 21mm;
            width: 168mm;
            font-size: 10pt;
            line-height: 1.45;
        }

        .table-wrap {
            position: absolute;
            top: 134mm;
            left: 21mm;
            width: 168mm;
        }

        .table-wrap.continuation {
            top: 30mm;
        }

        table.items {
            width: 168mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.items th {
            background: #d2cbc7;
            font-size: 10pt;
            font-weight: 700;
            padding: 3mm 2mm;
            border-bottom: 1px solid #aaa;
        }

        table.items td {
            font-size: 10pt;
            padding: 3mm 2mm;
            border-bottom: 1px solid #c8c0bc;
            vertical-align: top;
        }

        table.items th:nth-child(1),
        table.items td:nth-child(1) {
            width: 42mm;
            text-align: center;
        }

        table.items th:nth-child(2),
        table.items td:nth-child(2) {
            width: 126mm;
            text-align: left;
        }

        .delivery-final-note {
            position: absolute;
            left: 21mm;
            right: 21mm;
            bottom: 22mm;
            font-size: 10pt;
            line-height: 1.4;
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
<body>
@php
    $shippingMethodText = trim((string) ($offer->shipping_method ?: ($template->delivery_shipping_method_text ?: 'Lieferung oder Abholung')));
    $deliveryTitle = trim((string) ($template->delivery_title ?: 'Delivery Notice'));
    $dateLabel = trim((string) ($template->delivery_date_label ?: 'Date:'));
    $customerNumberLabel = trim((string) ($template->delivery_customer_number_label ?: 'Customer Nr.:'));
    $orderNumberLabel = trim((string) ($template->delivery_order_number_label ?: 'Order Nr.:'));
    $shippingMethodLabel = trim((string) ($template->delivery_shipping_method_label ?: 'Shipping Method:'));
    $quantityLabel = trim((string) ($template->delivery_quantity_label ?: 'Quantity'));
    $productLabel = trim((string) ($template->delivery_product_label ?: 'Product'));
    $introText = trim((string) ($template->delivery_intro_text ?? ''));
    $footerText = trim((string) ($template->delivery_footer_text ?? ''));

    $deliveryAddressLines = [];
    if ($offer->customer) {
        $customer = $offer->customer;

        if (! empty($customer->company_name)) {
            $deliveryAddressLines[] = $customer->company_name;
        }

        if (! empty($customer->contact_person)) {
            $deliveryAddressLines[] = $customer->contact_person;
        }

        $deliveryStreetLine = trim((string) (($customer->delivery_street ?? '') . ' ' . ($customer->delivery_house_number ?? '')));
        $deliveryCityLine = trim((string) (($customer->delivery_postal_code ?? '') . ' ' . ($customer->delivery_city ?? '')));

        if ($deliveryStreetLine !== '') $deliveryAddressLines[] = $deliveryStreetLine;
        if ($deliveryCityLine !== '') $deliveryAddressLines[] = $deliveryCityLine;
        if (! empty($customer->delivery_country)) $deliveryAddressLines[] = $customer->delivery_country;

        if (count($deliveryAddressLines) <= 1 && ! empty($customer->delivery_address)) {
            $deliveryAddressLines = array_values(array_filter(
                preg_split('/\R/u', trim((string) $customer->delivery_address)),
                fn ($line) => trim((string) $line) !== ''
            ));
        }

        if (empty($deliveryAddressLines)) {
            if (! empty($customer->company_name)) $deliveryAddressLines[] = $customer->company_name;

            $billingStreetLine = trim((string) (($customer->billing_street ?? '') . ' ' . ($customer->billing_house_number ?? '')));
            $billingCityLine = trim((string) (($customer->billing_postal_code ?? '') . ' ' . ($customer->billing_city ?? '')));

            if ($billingStreetLine !== '') $deliveryAddressLines[] = $billingStreetLine;
            if ($billingCityLine !== '') $deliveryAddressLines[] = $billingCityLine;
            if (! empty($customer->billing_country)) $deliveryAddressLines[] = $customer->billing_country;
        }

        if (empty($deliveryAddressLines) && ! empty($customer->city)) {
            $deliveryAddressLines[] = $customer->city;
        }
    }

    $unitShortLabels = [
        'gram' => 'g',
        'Gramm' => 'g',
        'liter' => 'L',
        'Liter' => 'L',
        'piece' => 'St.',
        'Stück' => 'St.',
    ];

    $formatQuantity = function ($item) use ($unitShortLabels) {
        $quantity = rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',');
        $unit = $unitShortLabels[$item->product?->unit ?? ''] ?? ($item->product?->unit ?? '');

        return trim($quantity . ' ' . $unit);
    };

    $items = $offer->items->values();

    $chunks = [];
    $remaining = $items;

    $chunks[] = [
        'items' => $remaining->take($introText ? 11 : 14),
        'first' => true,
    ];

    $remaining = $remaining->slice($introText ? 11 : 14)->values();

    while ($remaining->count() > 0) {
        $chunks[] = [
            'items' => $remaining->take(24),
            'first' => false,
        ];

        $remaining = $remaining->slice(24)->values();
    }
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
            <div class="meta">
                <strong>{{ $dateLabel }}</strong> {{ now()->format('d.m.Y') }}<br>
                <strong>{{ $customerNumberLabel }}</strong> {{ $offer->customer?->customer_number ?? '—' }}<br>
                <strong>{{ $orderNumberLabel }}</strong> {{ $offer->offer_number }}<br><br>
                <strong>{{ $shippingMethodLabel }}</strong> {{ $shippingMethodText }}
            </div>

            <div class="customer">
                @foreach ($deliveryAddressLines as $line)
                    {{ $line }}<br>
                @endforeach
            </div>

            <div class="title">{{ $deliveryTitle }}</div>

            @if ($introText)
                <div class="intro">{!! nl2br(e($introText)) !!}</div>
            @endif
        @endif

        <div class="table-wrap {{ $chunk['first'] ? '' : 'continuation' }}">
            <table class="items">
                <thead>
                    <tr>
                        <th>{{ $quantityLabel }}</th>
                        <th>{{ $productLabel }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($chunk['items'] as $item)
                        <tr>
                            <td>{{ $formatQuantity($item) }}</td>
                            <td>{{ $item->product_name ?? $item->description ?? $item->product?->name ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($loop->last && $footerText)
            <div class="delivery-final-note">{!! nl2br(e($footerText)) !!}</div>
        @endif
    </div>
@endforeach
</body>
</html>
