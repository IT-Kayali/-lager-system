@csrf

<div class="system-user-form">
    @if (session('error'))
        <div class="premium-alert system-user-form-alert is-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="system-user-form-section">
        <div class="system-user-form-section-head">
            <div>
                <p class="system-user-form-kicker">Zugang & Rolle</p>
                <h3>Benutzerdaten</h3>
            </div>
            <span class="system-user-form-section-icon"><i class="bi bi-person-badge"></i></span>
        </div>

        <div class="system-user-form-grid">
            <div class="premium-form-field">
                <label for="name">Name <span>*</span></label>
                <input
                    id="name"
                    name="name"
                    class="premium-input @error('name') premium-invalid-field @enderror"
                    value="{{ old('name', $user->name) }}"
                    required
                    autocomplete="name"
                >
                @error('name')
                    <div class="system-user-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="premium-form-field">
                <label for="email">E-Mail <span>*</span></label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="premium-input @error('email') premium-invalid-field @enderror"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="email"
                >
                @error('email')
                    <div class="system-user-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="premium-form-field">
                <label for="role">Rolle <span>*</span></label>
                <select
                    id="role"
                    name="role"
                    class="premium-select @error('role') premium-invalid-field @enderror"
                    required
                >
                    @foreach ($roles as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(old('role', $user->role) === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                @error('role')
                    <div class="system-user-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="premium-form-field">
                <label for="is_active">Kontostatus <span>*</span></label>

                @if ($user->exists && $user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">

                    <select id="is_active" class="premium-select" disabled>
                        <option value="1" selected>Aktiv</option>
                    </select>

                    <div class="system-user-form-help">
                        <i class="bi bi-shield-lock"></i>
                        Der eigene Benutzer kann nicht deaktiviert werden.
                    </div>
                @else
                    <select
                        id="is_active"
                        name="is_active"
                        class="premium-select @error('is_active') premium-invalid-field @enderror"
                        required
                    >
                        <option
                            value="1"
                            @selected((string) old('is_active', ($user->is_active ?? true) ? '1' : '0') === '1')
                        >
                            Aktiv
                        </option>
                        <option
                            value="0"
                            @selected((string) old('is_active', ($user->is_active ?? true) ? '1' : '0') === '0')
                        >
                            Deaktiviert
                        </option>
                    </select>
                @endif

                @error('is_active')
                    <div class="system-user-form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="system-user-form-section system-user-password-section">
        <div class="system-user-form-section-head">
            <div>
                <p class="system-user-form-kicker">Sicherheit</p>
                <h3>{{ $user->exists ? 'Passwort zurücksetzen' : 'Passwort festlegen' }}</h3>
                <p class="system-user-form-section-copy">
                    {{ $user->exists
                        ? 'Nur ausfüllen, wenn das bestehende Passwort dieses Benutzers geändert werden soll.'
                        : 'Vergib ein sicheres Passwort für den neuen Benutzer.' }}
                </p>
            </div>
            <span class="system-user-form-section-icon"><i class="bi bi-key"></i></span>
        </div>

        <div class="system-user-form-grid">
            <div class="premium-form-field">
                <label for="password">
                    {{ $user->exists ? 'Neues Passwort' : 'Passwort' }}
                    @if ($user->exists)
                        <span class="optional">(optional)</span>
                    @else
                        <span>*</span>
                    @endif
                </label>

                <div class="system-user-password-control">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="premium-input @error('password') premium-invalid-field @enderror"
                        autocomplete="new-password"
                        @required(! $user->exists)
                    >
                    <i class="bi bi-lock"></i>
                </div>

                @error('password')
                    <div class="system-user-form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="premium-form-field">
                <label for="password_confirmation">
                    Passwort bestätigen
                    @if (! $user->exists)
                        <span>*</span>
                    @endif
                </label>

                <div class="system-user-password-control">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="premium-input @error('password_confirmation') premium-invalid-field @enderror"
                        autocomplete="new-password"
                        @required(! $user->exists)
                    >
                    <i class="bi bi-shield-check"></i>
                </div>

                @error('password_confirmation')
                    <div class="system-user-form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    @if ($user->exists)
        <div class="system-user-form-security-note">
            <i class="bi bi-shield-check"></i>
            <div>
                <strong>Sitzungsschutz aktiv</strong>
                <span>
                    Wird Status, Rolle oder Passwort geändert, werden bestehende
                    Anmeldesitzungen dieses Benutzers automatisch beendet.
                </span>
            </div>
        </div>
    @endif

    <div class="system-user-form-actions">
        <button class="premium-btn gold" type="submit">
            <i class="bi bi-check2-circle"></i>
            {{ $user->exists ? 'Änderungen speichern' : 'Benutzer erstellen' }}
        </button>

        <a href="{{ route('security.index') }}" class="premium-btn">
            <i class="bi bi-arrow-left"></i>
            Zurück
        </a>
    </div>
</div>

<style>
    .system-user-form {
        display: grid;
        gap: 18px;
        color: #211d17;
    }

    .system-user-form-alert {
        margin: 0;
    }

    .system-user-form-alert.is-error {
        border-color: rgba(239, 68, 68, .25);
        background: rgba(239, 68, 68, .10);
        color: #991b1b;
    }

    .system-user-form-section {
        padding: 20px;
        border: 1px solid #e3d6c2;
        border-radius: 18px;
        background: #fffdf8;
    }

    .system-user-password-section {
        background: linear-gradient(180deg, #fffdf8 0%, #fffaf0 100%);
    }

    .system-user-form-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid #eadfce;
    }

    .system-user-form-kicker {
        margin: 0 0 4px;
        color: #8a6a00;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .system-user-form-section-head h3 {
        margin: 0;
        color: #111;
        font-size: 18px;
        font-weight: 950;
    }

    .system-user-form-section-copy {
        max-width: 700px;
        margin: 6px 0 0;
        color: #665f54;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.5;
    }

    .system-user-form-section-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: #f3e8be;
        color: #8a6a00;
        border: 1px solid #dfc96f;
        font-size: 18px;
    }

    .system-user-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .system-user-form .premium-form-field {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .system-user-form .premium-form-field label {
        margin: 0;
        color: #211d17 !important;
        font-size: 12px;
        font-weight: 950;
    }

    .system-user-form .premium-form-field label > span:not(.optional) {
        color: #b91c1c !important;
    }

    .system-user-form .premium-form-field label .optional {
        color: #766d60 !important;
        font-weight: 750;
    }

    .system-user-form .premium-input,
    .system-user-form .premium-select {
        width: 100% !important;
        min-height: 48px !important;
        height: 48px !important;
        border: 1px solid #d9c9ae !important;
        border-radius: 13px !important;
        background: #fffdf8 !important;
        color: #111 !important;
        box-shadow: none !important;
        font-weight: 750 !important;
    }

    .system-user-form .premium-input:focus,
    .system-user-form .premium-select:focus {
        border-color: #d4ad16 !important;
        box-shadow: 0 0 0 4px rgba(212, 173, 22, .14) !important;
    }

    .system-user-password-control {
        position: relative;
    }

    .system-user-password-control .premium-input {
        padding-right: 44px !important;
    }

    .system-user-password-control > i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #8a806f;
        pointer-events: none;
    }

    .system-user-form-help {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #665f54 !important;
        font-size: 11px;
        font-weight: 750;
        line-height: 1.4;
    }

    .system-user-form-error {
        color: #b91c1c;
        font-size: 11px;
        font-weight: 800;
    }

    .system-user-form-security-note {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 12px;
        align-items: center;
        padding: 14px;
        border: 1px solid #ead89e;
        border-radius: 14px;
        background: #fff9e8;
    }

    .system-user-form-security-note > i {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 11px;
        background: #f3e8be;
        color: #8a6a00;
    }

    .system-user-form-security-note strong,
    .system-user-form-security-note span {
        display: block;
        color: #211d17 !important;
    }

    .system-user-form-security-note strong {
        font-size: 12px;
        font-weight: 950;
    }

    .system-user-form-security-note span {
        margin-top: 3px;
        color: #665f54 !important;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.45;
    }

    .system-user-form-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .system-user-form-actions .premium-btn {
        min-height: 46px;
        padding: 0 18px;
    }

    @media (max-width: 720px) {
        .system-user-form-grid {
            grid-template-columns: 1fr;
        }

        .system-user-form-section {
            padding: 16px;
        }

        .system-user-form-actions {
            display: grid;
            grid-template-columns: 1fr;
        }

        .system-user-form-actions .premium-btn {
            width: 100%;
        }
    }
</style>
