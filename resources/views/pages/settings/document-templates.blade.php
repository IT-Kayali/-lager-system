<x-layouts.premium title="PDF-Vorlagen" subtitle="Firmendaten, Logo, Zahlungsinformationen und Footer für Angebote und Rechnungen bearbeiten.">
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

                        <div class="premium-form-field">
                            <label>Adresse</label>
                            <textarea name="company_address" rows="3" class="premium-textarea">{{ old('company_address', $template->company_address) }}</textarea>
                            @error('company_address') <div class="premium-error">{{ $message }}</div> @enderror
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
                            <label>Logo hochladen</label>
                            <input name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="premium-input">
                            <div class="premium-muted" style="margin-top:6px;">
                                Empfohlen: PNG oder JPG, transparente Logos funktionieren gut.
                            </div>
                            @error('logo') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="premium-form-field">
                            <label>Zahlungsinformationen</label>
                            <textarea name="payment_info" rows="3" class="premium-textarea">{{ old('payment_info', $template->payment_info) }}</textarea>
                            @error('payment_info') <div class="premium-error">{{ $message }}</div> @enderror
                        </div>

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
</x-layouts.premium>
