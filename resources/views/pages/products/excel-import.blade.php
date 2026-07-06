<x-layouts.premium title="Produkte importieren" subtitle="Importiere Produkte und Preisstaffeln aus einer Excel-Datei.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            {{ session('error') }}
        </div>
    @endif

    @if (session('import_errors'))
        <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
            <strong>Import-Fehler:</strong>
            <ul style="margin:10px 0 0 18px;">
                @foreach (session('import_errors') as $importError)
                    <li>{{ $importError }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="premium-card">
        <form method="POST" action="{{ route('products.excel.import') }}" enctype="multipart/form-data">
            @csrf

            <div class="premium-form-grid">
                <div class="premium-form-field full">
                    <label for="excel_file">Excel-Datei (.xlsx)</label>
                    <input
                        id="excel_file"
                        name="excel_file"
                        type="file"
                        class="premium-input"
                        accept=".xlsx,.xls"
                        required
                    >
                    @error('excel_file') <div class="premium-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-top:18px; display:flex; gap:10px; flex-wrap:wrap;">
                <button class="premium-btn gold" type="submit">
                    <i class="bi bi-upload"></i>
                    Excel importieren
                </button>

                <a href="{{ route('products.index') }}" class="premium-btn">
                    <i class="bi bi-arrow-left"></i>
                    Zurück
                </a>

                <a href="{{ route('products.excel.export') }}" class="premium-btn">
                    <i class="bi bi-download"></i>
                    Vorlage exportieren
                </a>
            </div>
        </form>
    </section>

    <section class="premium-card" style="margin-top:18px;">
        <h2 style="margin-top:0;">Excel-Aufbau</h2>

        <p class="premium-muted">
            Die Datei soll zwei Tabellenblätter enthalten:
            <strong>Produkte</strong> und <strong>Preisstaffeln</strong>.
            Am einfachsten exportierst du zuerst eine Vorlage und bearbeitest diese Datei.
        </p>

        <h3>Blatt Produkte</h3>
        <pre style="white-space:pre-wrap;">product_code | name | manufacturer_designation | serial_number | unit | supplier | minimum_stock | description</pre>

        <h3>Blatt Preisstaffeln</h3>
        <pre style="white-space:pre-wrap;">product_code | customer_group | tier_key | tier_label | min_grams | max_grams | price</pre>

        <p class="premium-muted">
            Erlaubte Werte für <strong>unit</strong>: gram, liter, piece.
            Produkte und Preise werden aktualisiert oder angelegt. Es wird nichts automatisch gelöscht.
        </p>
    </section>
</x-layouts.premium>
