<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; size: A4 portrait; }

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

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: 'ExoPDF', DejaVu Sans, sans-serif;
            color: #111;
            background: #fff;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            overflow: hidden;
            background: #fff;
        }

        .page:last-child { page-break-after: auto; }

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
            top: 16mm;
            right: 34mm;
            width: 45mm;
            max-height: 28mm;
            object-fit: contain;
        }

        .sender-line {
            position: absolute;
            top: 39mm;
            left: 18mm;
            width: 95mm;
            font-size: 7.8pt;
            color: #333;
            border-bottom: 1px solid #bbb;
            padding-bottom: 1mm;
        }

        .recipient {
            position: absolute;
            top: 48mm;
            left: 18mm;
            width: 92mm;
            font-size: 10pt;
            line-height: 1.35;
        }

        .right-info {
            position: absolute;
            top: 45mm;
            right: 18mm;
            width: 72mm;
            font-size: 10pt;
            line-height: 1.45;
        }

        .right-info .row {
            display: block;
            margin-bottom: 1.2mm;
        }

        .right-info strong {
            display: inline-block;
            min-width: 28mm;
            font-weight: 900;
        }

        .title {
            position: absolute;
            top: 99mm;
            left: 18mm;
            right: 18mm;
            text-align: center;
            font-size: 19pt;
            font-weight: 900;
        }

        .intro {
            position: absolute;
            top: 116mm;
            left: 18mm;
            width: 174mm;
            font-size: 10pt;
            line-height: 1.45;
        }

        .table-wrap {
            position: absolute;
            top: 140mm;
            left: 18mm;
            width: 174mm;
        }

        .table-wrap.continuation {
            top: 30mm;
        }

        table.items {
            width: 174mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.items th {
            background: #f1f1f1;
            font-size: 10pt;
            font-weight: 900;
            padding: 3mm 2mm;
            border-bottom: 1px solid #999;
        }

        table.items td {
            font-size: 10pt;
            padding: 3mm 2mm;
            border-bottom: 1px solid #cfcfcf;
            vertical-align: top;
        }

        table.items th:nth-child(1),
        table.items td:nth-child(1) {
            width: 38mm;
            text-align: center;
        }

        table.items th:nth-child(2),
        table.items td:nth-child(2) {
            width: 136mm;
            text-align: left;
        }

        .delivery-final-note {
            position: absolute;
            left: 18mm;
            right: 18mm;
            bottom: 52mm;
            font-size: 10pt;
            line-height: 1.4;
        }

        .footer {
            position: absolute;
            left: 18mm;
            right: 18mm;
            bottom: 13mm;
            font-size: 7.3pt;
            line-height: 1.35;
            color: #333;
            border-top: 1px solid #d9d9d9;
            padding-top: 3mm;
        }

        .footer-table {
            width: 174mm;
            border-collapse: collapse;
        }

        .footer-table td {
            width: 33.333%;
            vertical-align: top;
            padding-right: 8mm;
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
<body>
@php
    $deliveryTitle = trim((string) ($template->delivery_title ?: 'Lieferschein'));
    $dateLabel = trim((string) ($template->delivery_date_label ?: 'Datum:'));
    $customerNumberLabel = trim((string) ($template->delivery_customer_number_label ?: 'Kunden-Nr.:'));
    $orderNumberLabel = trim((string) ($template->delivery_order_number_label ?: 'Bestell-Nr.:'));
    $shippingMethodLabel = trim((string) ($template->delivery_shipping_method_label ?: 'Versandart:'));
    $shippingMethodText = trim((string) ($template->delivery_shipping_method_text ?: 'Lieferung oder Abholung'));
    $quantityLabel = trim((string) ($template->delivery_quantity_label ?: 'Menge'));
    $productLabel = trim((string) ($template->delivery_product_label ?: 'Bezeichnung'));
    $introText = trim((string) ($template->delivery_intro_text ?: "Sehr geehrte Damen und Herren,\n\nvielen Dank für Ihre Bestellung. Wir liefern Ihnen wie vereinbart folgende Waren:"));
    $footerText = trim((string) ($template->delivery_footer_text ?: 'Die gelieferte Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.'));

    $autoFooterLeft = trim(implode("\n", array_filter([
        $template->company_name ?? null,
        trim((string) (($template->company_street ?? '') . ' ' . ($template->company_house_number ?? ''))) ?: null,
        trim((string) (($template->company_postal_code ?? '') . ' ' . ($template->company_city ?? ''))) ?: null,
        $template->company_country ?? null,
    ])));

    $autoFooterMiddle = trim(implode("\n", array_filter([
        'Kontakt:',
        $template->company_phone ? 'Tel: ' . $template->company_phone : null,
        $template->company_email ? 'E-Mail: ' . $template->company_email : null,
        $template->company_website ? 'Web: ' . $template->company_website : null,
    ])));

    $autoFooterRight = trim(implode("\n", array_filter([
        $template->company_vat_id ? 'USt-IdNr.: ' . $template->company_vat_id : null,
        $template->footer_note ?: null,
    ])));

    $deliveryFooterLeft = trim((string) ($template->delivery_footer_left_text ?: $autoFooterLeft));
    $deliveryFooterMiddle = trim((string) ($template->delivery_footer_middle_text ?: $autoFooterMiddle));
    $deliveryFooterRight = trim((string) ($template->delivery_footer_right_text ?: $autoFooterRight));

    $companyStreetLine = trim((string) (($template->company_street ?? '') . ' ' . ($template->company_house_number ?? '')));
    $companyCityLine = trim((string) (($template->company_postal_code ?? '') . ' ' . ($template->company_city ?? '')));
    $senderLine = trim(implode(' – ', array_filter([
        $template->company_name ?? null,
        $companyStreetLine ?: null,
        $companyCityLine ?: null,
    ])));

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

        if (empty($deliveryAddressLines) && ! empty($customer->billing_address)) {
            $deliveryAddressLines = array_values(array_filter(
                preg_split('/\R/u', trim((string) $customer->billing_address)),
                fn ($line) => trim((string) $line) !== ''
            ));
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
        'items' => $remaining->take(8),
        'first' => true,
    ];

    $remaining = $remaining->slice(8)->values();

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
        {{-- PDF_HARD_BACKGROUND_IMAGE_START --}}
        @if (! empty($backgroundDataUri))
            <img class="pdf-hard-background" src="{{ $backgroundDataUri }}" alt="">
        @endif
        {{-- PDF_HARD_BACKGROUND_IMAGE_END --}}
        @if ($backgroundDataUri)
            <img class="background" src="{{ $backgroundDataUri }}" alt="">
        @endif

        <div class="content">
            @if ($chunk['first'])
                @if ($logoDataUri)
                    <img class="logo" src="{{ $logoDataUri }}" alt="">
                @endif

                @if ($senderLine)
                    <div class="sender-line">{{ $senderLine }}</div>
                @endif

                <div class="recipient">
                    @foreach ($deliveryAddressLines as $line)
                        {{ $line }}<br>
                    @endforeach
                </div>

                <div class="right-info">
                    <span class="row"><strong>{{ $dateLabel }}</strong> {{ now()->format('d.m.Y') }}</span>
                    <span class="row"><strong>{{ $customerNumberLabel }}</strong> {{ $offer->customer?->customer_number ?? '—' }}</span>
                    <span class="row"><strong>{{ $orderNumberLabel }}</strong> {{ $offer->offer_number }}</span>
                    <br>
                    <span class="row"><strong>{{ $shippingMethodLabel }}</strong> {{ $shippingMethodText }}</span>
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

            @if ($template->show_company_details)
                <div class="footer">
                    <table class="footer-table">
                        <tr>
                            <td>{!! nl2br(e($deliveryFooterLeft)) !!}</td>
                            <td>{!! nl2br(e($deliveryFooterMiddle)) !!}</td>
                            <td>{!! nl2br(e($deliveryFooterRight)) !!}</td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endforeach
</body>
</html>
