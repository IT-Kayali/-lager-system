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