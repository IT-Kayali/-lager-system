<x-layouts.premium title="PDF-Vorlagen" subtitle="Firmendaten, Logo, Footer und Lieferschein-Texte bearbeiten.">
    @if (session('success'))
        <div class="premium-alert">{{ session('success') }}</div>
    @endif

    <div class="premium-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        @foreach ($templates as $template)
            <section class="premium-card">
                <h2 style="font-size:20px; font-weight:900; margin:0 0 14px;">
                    {{ $template->name }}
                </h2>

                @if ($template->logo_path)
                    <div style="margin-bottom:16px;">
                        <div class="premium-muted" style="margin-bottom:8px;">Aktuelles Logo</div>
                        <div style="background:#fffaf1; border:1px solid rgba(227,202,110,.45); border-radius:18px; padding:14px; display:inline-flex;">
                            <img src="{{ asset('storage/' . $template->logo_path) }}" alt="Logo" style="max-width:180px; max-height:90px; object-fit:contain;">
                        </div>
                    </div>
                @endif
                                        @if ($template->logo_path)
                                            <label class="pdf-asset-remove-box">
                                                <input type="checkbox" name="remove_logo" value="1">
                                                Logo Angebot/Rechnung entfernen
                                            </label>
                                        @endif


                <form method="POST" action="{{ route('document-templates.update', $template) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="premium-form-grid" style="grid-template-columns:1fr;">
                        <div class="premium-form-field">
                            <label>Name der Vorlage *</label>
                            <input name="name" class="premium-input" value="{{ old('name', $template->name) }}" required>
                            @error('name') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="premium-form-field">
                            <label>Firmenname</label>
                            <input name="company_name" class="premium-input" value="{{ old('company_name', $template->company_name) }}">
                            @error('company_name') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="premium-card" style="box-shadow:none;">
                            <h3 style="font-size:18px; font-weight:900; margin:0 0 14px;">Firmenadresse</h3>

                            <div class="premium-form-grid">
                                <div class="premium-form-field">
                                    <label>Straße</label>
                                    <input name="company_street" class="premium-input" value="{{ old('company_street', $template->company_street) }}">
                                    @error('company_street') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Hausnummer</label>
                                    <input name="company_house_number" class="premium-input" value="{{ old('company_house_number', $template->company_house_number) }}">
                                    @error('company_house_number') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>PLZ</label>
                                    <input name="company_postal_code" class="premium-input" value="{{ old('company_postal_code', $template->company_postal_code) }}">
                                    @error('company_postal_code') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Stadt</label>
                                    <input name="company_city" class="premium-input" value="{{ old('company_city', $template->company_city) }}">
                                    @error('company_city') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field full">
                                    <label>Land</label>
                                    <input name="company_country" class="premium-input" value="{{ old('company_country', $template->company_country ?: 'Deutschland') }}">
                                    @error('company_country') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="premium-form-field">
                            <label>Telefon</label>
                            <input name="company_phone" class="premium-input" value="{{ old('company_phone', $template->company_phone) }}">
                            @error('company_phone') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="premium-form-field">
                            <label>E-Mail</label>
                            <input name="company_email" type="email" class="premium-input" value="{{ old('company_email', $template->company_email) }}">
                            @error('company_email') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="premium-form-field">
                            <label>USt-ID</label>
                            <input name="company_vat_id" class="premium-input" value="{{ old('company_vat_id', $template->company_vat_id) }}" placeholder="z. B. DE319000676">
                            @error('company_vat_id') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="premium-form-field">
                            <label>Website</label>
                            <input name="company_website" class="premium-input" value="{{ old('company_website', $template->company_website) }}" placeholder="z. B. www.alowidat.de">
                            @error('company_website') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="premium-form-field">
                            <label>Logo Angebot/Rechnung hochladen</label>
                            <input name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="premium-input">
                            <div class="premium-muted" style="margin-top:6px;">
                                Empfohlen: PNG oder JPG, transparente Logos funktionieren gut.
                            </div>
                            @error('logo') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="premium-form-field">
                            <label>PDF-Hintergrund Angebot/Rechnung hochladen</label>
                            <input name="background_image" type="file" accept="image/png,image/jpeg,image/webp" class="premium-input">

                            @if ($template->background_image_path)
                                <div style="margin-top:12px;">
                                    <div class="premium-muted" style="margin-bottom:8px;">Aktueller Hintergrund</div>
                                    <div style="background:#fffaf1; border:1px solid rgba(227,202,110,.45); border-radius:18px; padding:14px; display:inline-flex;">
                                        <img src="{{ asset('storage/' . $template->background_image_path) }}" alt="PDF Hintergrund" style="max-width:180px; max-height:120px; object-fit:contain;">
                                    </div>
                                </div>
                            @endif
                                        @if ($template->background_image_path)
                                            <label class="pdf-asset-remove-box">
                                                <input type="checkbox" name="remove_background_image" value="1">
                                                Hintergrund Angebot/Rechnung entfernen
                                            </label>
                                        @endif


                            <div class="premium-muted" style="margin-top:6px;">
                                Empfohlen: A4 Hochformat als JPG oder PNG. Dieses Bild wird im PDF als fixer Hintergrund verwendet.
                            </div>

                            @error('background_image') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        @if ($template->show_logo)
                            <div class="premium-form-field">
                                <label>MwSt. (%)</label>
                                <input name="tax_rate" type="number" step="0.01" min="0" max="100" class="premium-input" value="{{ old('tax_rate', $template->tax_rate ?? 19) }}">
                                @error('tax_rate') <div class="premium-error">{{ $message }}</div> @enderror
                            </div>
                        @endif







                        {{-- DELIVERY_NOTE_ASSETS_SETTINGS_START --}}
                        <div class="premium-card" style="box-shadow:none;">
                            <h3 style="font-size:18px; font-weight:900; margin:0 0 8px;">Lieferschein Logo & Hintergrund</h3>
                            <div class="premium-muted" style="margin-bottom:14px;">
                                Diese Dateien gelten nur für den Lieferschein. Angebot und Rechnung bleiben davon getrennt.
                            </div>

                            <div class="premium-form-grid">
                                <div class="premium-form-field">
                                    <label>Logo Lieferschein hochladen</label>
                                    <input name="delivery_logo" type="file" accept="image/png,image/jpeg,image/webp" class="premium-input">

                                    @if ($template->delivery_logo_path)
                                        <div style="margin-top:12px;">
                                            <div class="premium-muted" style="margin-bottom:8px;">Aktuelles Lieferschein-Logo</div>
                                            <div style="background:#fffaf1; border:1px solid rgba(227,202,110,.45); border-radius:18px; padding:14px; display:inline-flex;">
                                                <img src="{{ asset('storage/' . $template->delivery_logo_path) }}" alt="Lieferschein Logo" style="max-width:180px; max-height:90px; object-fit:contain;">
                                            </div>
                                        </div>
                                    @endif
                                        @if ($template->delivery_logo_path)
                                            <label class="pdf-asset-remove-box">
                                                <input type="checkbox" name="remove_delivery_logo" value="1">
                                                Logo Lieferschein entfernen
                                            </label>
                                        @endif


                                    @error('delivery_logo') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>PDF-Hintergrund Lieferschein hochladen</label>
                                    <input name="delivery_background_image" type="file" accept="image/png,image/jpeg,image/webp" class="premium-input">

                                    @if ($template->delivery_background_image_path)
                                        <div style="margin-top:12px;">
                                            <div class="premium-muted" style="margin-bottom:8px;">Aktueller Lieferschein-Hintergrund</div>
                                            <div style="background:#fffaf1; border:1px solid rgba(227,202,110,.45); border-radius:18px; padding:14px; display:inline-flex;">
                                                <img src="{{ asset('storage/' . $template->delivery_background_image_path) }}" alt="Lieferschein Hintergrund" style="max-width:180px; max-height:120px; object-fit:contain;">
                                            </div>
                                        </div>
                                    @endif
                                        @if ($template->delivery_background_image_path)
                                            <label class="pdf-asset-remove-box">
                                                <input type="checkbox" name="remove_delivery_background_image" value="1">
                                                Hintergrund Lieferschein entfernen
                                            </label>
                                        @endif


                                    <div class="premium-muted" style="margin-top:6px;">
                                        Empfohlen: A4 Hochformat als JPG oder PNG. Dieser Hintergrund wird nur im Lieferschein verwendet.
                                    </div>

                                    @error('delivery_background_image') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        {{-- DELIVERY_NOTE_ASSETS_SETTINGS_END --}}

                        {{-- DELIVERY_NOTE_TEMPLATE_SETTINGS_START --}}
                        <div class="premium-card" style="box-shadow:none;">
                            <h3 style="font-size:18px; font-weight:900; margin:0 0 8px;">Lieferschein</h3>
                            <div class="premium-muted" style="margin-bottom:14px;">
                                Texte und Spalten für den Lieferschein dieser Vorlage.
                            </div>

                            <div class="premium-form-grid">
                                <div class="premium-form-field">
                                    <label>Titel</label>
                                    <input name="delivery_title" class="premium-input" value="{{ old('delivery_title', $template->delivery_title ?: ($template->show_logo ? 'Lieferschein' : 'Delivery Notice')) }}">
                                    @error('delivery_title') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Datum-Label</label>
                                    <input name="delivery_date_label" class="premium-input" value="{{ old('delivery_date_label', $template->delivery_date_label ?: ($template->show_logo ? 'Datum:' : 'Date:')) }}">
                                    @error('delivery_date_label') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Kunden-Nr.-Label</label>
                                    <input name="delivery_customer_number_label" class="premium-input" value="{{ old('delivery_customer_number_label', $template->delivery_customer_number_label ?: ($template->show_logo ? 'Kunden-Nr.:' : 'Customer Nr.:')) }}">
                                    @error('delivery_customer_number_label') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Bestell-Nr.-Label</label>
                                    <input name="delivery_order_number_label" class="premium-input" value="{{ old('delivery_order_number_label', $template->delivery_order_number_label ?: ($template->show_logo ? 'Bestell-Nr.:' : 'Order Nr.:')) }}">
                                    @error('delivery_order_number_label') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Versandart-Label</label>
                                    <input name="delivery_shipping_method_label" class="premium-input" value="{{ old('delivery_shipping_method_label', $template->delivery_shipping_method_label ?: ($template->show_logo ? 'Versandart:' : 'Shipping Method:')) }}">
                                    @error('delivery_shipping_method_label') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Versandart-Text</label>
                                    <input name="delivery_shipping_method_text" class="premium-input" value="{{ old('delivery_shipping_method_text', $template->delivery_shipping_method_text ?: 'Lieferung oder Abholung') }}">
                                    @error('delivery_shipping_method_text') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Spalte Menge</label>
                                    <input name="delivery_quantity_label" class="premium-input" value="{{ old('delivery_quantity_label', $template->delivery_quantity_label ?: ($template->show_logo ? 'Menge' : 'Quantity')) }}">
                                    @error('delivery_quantity_label') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field">
                                    <label>Spalte Produkt / Bezeichnung</label>
                                    <input name="delivery_product_label" class="premium-input" value="{{ old('delivery_product_label', $template->delivery_product_label ?: ($template->show_logo ? 'Bezeichnung' : 'Product')) }}">
                                    @error('delivery_product_label') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field full">
                                    <label>Einleitungstext</label>
                                    <textarea name="delivery_intro_text" rows="4" class="premium-textarea">{{ old('delivery_intro_text', $template->delivery_intro_text) }}</textarea>
                                    <div class="premium-muted" style="margin-top:6px;">Mehrere Zeilen sind möglich.</div>
                                    @error('delivery_intro_text') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field full">
                                    <label>Schlusssatz / Hinweis</label>
                                    <textarea name="delivery_footer_text" rows="3" class="premium-textarea">{{ old('delivery_footer_text', $template->delivery_footer_text) }}</textarea>
                                    @error('delivery_footer_text') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                {{-- DELIVERY_FOOTER_EDIT_SETTINGS_START --}}
                                <div class="premium-form-field full">
                                    <label>Lieferschein-Footer links</label>
                                    <textarea name="delivery_footer_left_text" rows="5" class="premium-textarea" placeholder="z. B. Firmenname, Adresse, Land">{{ old('delivery_footer_left_text', $template->delivery_footer_left_text) }}</textarea>
                                    <div class="premium-muted" style="margin-top:6px;">Leer lassen = automatische Firmendaten verwenden.</div>
                                    @error('delivery_footer_left_text') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field full">
                                    <label>Lieferschein-Footer Mitte</label>
                                    <textarea name="delivery_footer_middle_text" rows="5" class="premium-textarea" placeholder="z. B. Kontakt, Telefon, E-Mail, Website">{{ old('delivery_footer_middle_text', $template->delivery_footer_middle_text) }}</textarea>
                                    <div class="premium-muted" style="margin-top:6px;">Leer lassen = automatische Kontaktdaten verwenden.</div>
                                    @error('delivery_footer_middle_text') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="premium-form-field full">
                                    <label>Lieferschein-Footer rechts</label>
                                    <textarea name="delivery_footer_right_text" rows="5" class="premium-textarea" placeholder="z. B. USt-ID, Finanzamt, zusätzliche Hinweise">{{ old('delivery_footer_right_text', $template->delivery_footer_right_text) }}</textarea>
                                    <div class="premium-muted" style="margin-top:6px;">Leer lassen = USt-ID und Footer-Hinweis verwenden.</div>
                                    @error('delivery_footer_right_text') <div class="premium-error">{{ $message }}</div> @enderror
                                </div>
                                {{-- DELIVERY_FOOTER_EDIT_SETTINGS_END --}}

                            </div>
                        </div>
                        {{-- DELIVERY_NOTE_TEMPLATE_SETTINGS_END --}}

                        <div class="premium-form-field">
                            <label>Footer-Hinweise</label>
                            <textarea name="footer_note" rows="3" class="premium-textarea">{{ old('footer_note', $template->footer_note) }}</textarea>
                            @error('footer_note') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <label style="display:flex; gap:10px; align-items:center; font-weight:800;">
                            <input type="checkbox" name="show_company_details" value="1" @checked(old('show_company_details', $template->show_company_details))>
                            Firmeninformationen anzeigen
                        </label>

                        <label style="display:flex; gap:10px; align-items:center; font-weight:800;">
                            <input type="checkbox" name="show_logo" value="1" @checked(old('show_logo', $template->show_logo))>
                            Logo anzeigen
                        </label>
                    </div>

                    <button class="premium-btn gold" type="submit" style="margin-top:18px;">
                        <i class="bi bi-save"></i>
                        Vorlage speichern
                    </button>
                </form>
            </section>
        @endforeach
    </div>

<style>
    /* PDF_ASSET_REMOVE_STYLE_START */
    .pdf-asset-remove-box {
        margin-top: 10px;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 10px 12px;
        border: 1px solid rgba(239,68,68,.25);
        background: rgba(239,68,68,.07);
        color: #991b1b;
        border-radius: 14px;
        font-weight: 900;
        cursor: pointer;
    }

    .pdf-asset-remove-box input {
        width: 16px;
        height: 16px;
        accent-color: #ef4444;
    }

    .pdf-asset-remove-box:hover {
        background: rgba(239,68,68,.12);
    }
    /* PDF_ASSET_REMOVE_STYLE_END */
</style>

</x-layouts.premium>
