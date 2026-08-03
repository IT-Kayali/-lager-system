<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 18mm 14mm 18mm; }

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

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'ExoPDF', DejaVu Sans, sans-serif;
            color: #171717;
            font-size: 10pt;
            line-height: 1.35;
        }

        .background {
            position: fixed;
            top: -18mm;
            left: -14mm;
            width: 210mm;
            height: 297mm;
            z-index: -2;
        }

        .header {
            width: 100%;
            margin-bottom: 18mm;
        }

        .header td { vertical-align: top; }
        .company { width: 62%; }

        .company-name {
            font-size: 17pt;
            font-weight: 700;
            margin-bottom: 2mm;
        }

        .company-lines {
            color: #4b463e;
            font-size: 9pt;
        }

        .logo-cell {
            width: 38%;
            text-align: right;
        }

        .logo {
            width: 42mm;
            max-height: 24mm;
            object-fit: contain;
        }

        .document-head {
            width: 100%;
            margin-bottom: 9mm;
            border-collapse: collapse;
        }

        .document-title {
            font-size: 22pt;
            font-weight: 700;
            margin: 0 0 3mm;
        }

        .recipient-box,
        .meta-box {
            border: 1px solid #d7c8aa;
            border-radius: 7px;
            background: rgba(255, 253, 248, .92);
            padding: 5mm;
        }

        .recipient-box { width: 58%; }
        .meta-box { width: 40%; }

        .recipient-label,
        .section-label {
            color: #8a6a00;
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .recipient-name {
            margin-top: 2mm;
            font-size: 15pt;
            font-weight: 700;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 1mm 0;
            vertical-align: top;
        }

        .meta-table td:first-child {
            width: 42%;
            font-weight: 700;
        }

        .status {
            display: inline-block;
            padding: 1.2mm 3mm;
            border-radius: 999px;
            background: #f3e8be;
            font-weight: 700;
        }

        .intro {
            margin: 0 0 7mm;
            color: #3d3932;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.items thead { display: table-header-group; }
        table.items tr { page-break-inside: avoid; }

        table.items th {
            padding: 3mm 2.5mm;
            background: #eee7dc;
            border-bottom: 1px solid #8d8069;
            text-align: left;
            font-size: 9pt;
            font-weight: 700;
        }

        table.items td {
            padding: 3mm 2.5mm;
            border-bottom: 1px solid #ded6c8;
            vertical-align: top;
        }

        .col-pos { width: 11mm; text-align: center; }
        .col-code { width: 31mm; }
        .col-qty { width: 36mm; text-align: right; }

        .product-name { font-weight: 700; }
        .product-code { color: #625a4d; font-size: 8.5pt; }

        .note-box {
            margin-top: 8mm;
            padding: 4mm 5mm;
            border: 1px solid #d7c8aa;
            border-radius: 7px;
            background: #fffdf8;
        }

        .note-box p { margin: 2mm 0 0; }

        .workflow-note {
            margin-top: 7mm;
            padding: 3.5mm 4mm;
            border-left: 3px solid #d4aa20;
            background: #faf5e7;
            color: #4a4338;
        }

        .signatures {
            width: 100%;
            margin-top: 19mm;
            border-collapse: collapse;
        }

        .signatures td {
            width: 48%;
            vertical-align: bottom;
        }

        .signature-line {
            margin-top: 16mm;
            border-top: 1px solid #575047;
            padding-top: 2mm;
            color: #575047;
            font-size: 8.5pt;
        }

        .footer {
            position: fixed;
            left: 14mm;
            right: 14mm;
            bottom: 7mm;
            border-top: 1px solid #d7c8aa;
            padding-top: 2.5mm;
            color: #6a6257;
            font-size: 7.5pt;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $companyName = trim((string) ($template->company_name ?: 'Alowidat Parfümerie'));
    $companyStreet = trim((string) (($template->company_street ?? '') . ' ' . ($template->company_house_number ?? '')));
    $companyCity = trim((string) (($template->company_postal_code ?? '') . ' ' . ($template->company_city ?? '')));
    $companyCountry = trim((string) ($template->company_country ?? 'Deutschland'));

    $companyLines = array_values(array_filter([
        $companyStreet,
        $companyCity,
        $companyCountry,
        $template->company_phone ? 'Tel: ' . $template->company_phone : null,
        $template->company_email ? 'E-Mail: ' . $template->company_email : null,
    ], fn ($line) => filled($line)));

    $statusLabel = \App\Models\BranchWithdrawal::statusLabels()[$withdrawal->status] ?? $withdrawal->status;
    $unitLabels = [
        'gram' => 'g',
        'liter' => 'L',
        'piece' => 'Stk.',
    ];

    $formatQuantity = function ($value): string {
        $formatted = number_format((float) $value, 3, ',', '.');
        return rtrim(rtrim($formatted, '0'), ',');
    };
@endphp

@if (! empty($backgroundDataUri))
    <img class="background" src="{{ $backgroundDataUri }}" alt="">
@endif

<table class="header">
    <tr>
        <td class="company">
            <div class="company-name">{{ $companyName }}</div>
            <div class="company-lines">
                @foreach ($companyLines as $line)
                    {{ $line }}<br>
                @endforeach
            </div>
        </td>
        <td class="logo-cell">
            @if (! empty($logoDataUri))
                <img class="logo" src="{{ $logoDataUri }}" alt="">
            @endif
        </td>
    </tr>
</table>

<div class="document-title">Filial-Lieferschein</div>

<table class="document-head">
    <tr>
        <td class="recipient-box">
            <div class="recipient-label">Empfänger</div>
            <div class="recipient-name">{{ $withdrawal->branch_name }}</div>
            <div style="margin-top:2mm;color:#5d5549;">Interne Warenübergabe an die Filiale</div>
        </td>
        <td style="width:2%;"></td>
        <td class="meta-box">
            <table class="meta-table">
                <tr><td>Nummer:</td><td>{{ $withdrawal->withdrawal_number }}</td></tr>
                <tr><td>Datum:</td><td>{{ $withdrawal->created_at?->format('d.m.Y') }}</td></tr>
                <tr><td>Status:</td><td><span class="status">{{ $statusLabel }}</span></td></tr>
                <tr><td>Erstellt von:</td><td>{{ $withdrawal->user?->name ?? 'System' }}</td></tr>
                <tr><td>Ausgegeben von:</td><td>{{ $withdrawal->processor?->name ?? 'Noch offen' }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<p class="intro">
    Folgende Waren sind für die oben genannte Filiale vorgesehen. Bitte Mengen bei der Übergabe prüfen und den Empfang bestätigen.
</p>

<table class="items">
    <thead>
        <tr>
            <th class="col-pos">Pos.</th>
            <th>Produkt</th>
            <th class="col-code">Artikelnummer</th>
            <th class="col-qty">Menge</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($withdrawal->items as $item)
            @php
                $unit = $unitLabels[$item->product?->unit] ?? ($item->product?->unit ?: '');
            @endphp
            <tr>
                <td class="col-pos">{{ $loop->iteration }}</td>
                <td><div class="product-name">{{ $item->product?->name ?? 'Gelöschtes Produkt' }}</div></td>
                <td class="col-code"><span class="product-code">{{ $item->product?->product_code ?: '—' }}</span></td>
                <td class="col-qty"><strong>{{ $formatQuantity($item->quantity) }} {{ $unit }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

@if (filled($withdrawal->note))
    <div class="note-box">
        <div class="section-label">Notiz</div>
        <p>{{ $withdrawal->note }}</p>
    </div>
@endif

<div class="workflow-note">
    @if ($withdrawal->isIssued())
        Der Lagerbestand wurde für diesen Filialausgang bereits per FIFO abgebucht.
    @else
        Der Lagerbestand ist für diesen Vorgang noch nicht abgebucht. Die Abbuchung erfolgt erst beim Status „Ausgegeben“.
    @endif
</div>

<table class="signatures">
    <tr>
        <td><div class="signature-line">Datum, Unterschrift Lager / Ausgabe</div></td>
        <td style="width:4%;"></td>
        <td><div class="signature-line">Datum, Unterschrift Filiale / Übernahme</div></td>
    </tr>
</table>

<div class="footer">
    {{ $companyName }} · Filial-Lieferschein {{ $withdrawal->withdrawal_number }}
</div>
</body>
</html>
