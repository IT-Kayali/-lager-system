@csrf

@if (session('error'))
    <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
        {{ session('error') }}
    </div>
@endif

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="name">Name *</label>
        <input
            id="name"
            name="name"
            class="premium-input"
            value="{{ old('name', $user->name) }}"
            required
        >
        @error('name')
            <div class="premium-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="premium-form-field">
        <label for="email">E-Mail *</label>
        <input
            id="email"
            name="email"
            type="email"
            class="premium-input"
            value="{{ old('email', $user->email) }}"
            required
        >
        @error('email')
            <div class="premium-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="premium-form-field">
        <label for="role">Rolle *</label>

        <select
            id="role"
            name="role"
            class="premium-select"
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
            <div class="premium-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="premium-form-field">
        <label for="is_active">Status *</label>

        @if ($user->exists && $user->id === auth()->id())
            <input type="hidden" name="is_active" value="1">

            <select
                id="is_active"
                class="premium-select"
                disabled
            >
                <option value="1" selected>Aktiv</option>
            </select>

            <div class="premium-muted" style="margin-top:6px; font-size:12px;">
                Der eigene Benutzer kann nicht deaktiviert werden.
            </div>
        @else
            <select
                id="is_active"
                name="is_active"
                class="premium-select"
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
            <div class="premium-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="premium-form-field">
        <label for="password">
            @if ($user->exists)
                Neues Passwort vergeben
                <span class="premium-muted">(optional)</span>
            @else
                Passwort *
            @endif
        </label>

        <input
            id="password"
            name="password"
            type="password"
            class="premium-input"
            autocomplete="new-password"
            @required(! $user->exists)
        >

        @if ($user->exists)
            <div class="premium-muted" style="margin-top:6px; font-size:12px;">
                Leer lassen, wenn das aktuelle Passwort unverändert bleiben soll.
            </div>
        @endif

        @error('password')
            <div class="premium-error">{{ $message }}</div>
        @enderror
    </div>

    <div class="premium-form-field">
        <label for="password_confirmation">
            @if ($user->exists)
                Neues Passwort bestätigen
            @else
                Passwort bestätigen *
            @endif
        </label>

        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            class="premium-input"
            autocomplete="new-password"
            @required(! $user->exists)
        >

        @error('password_confirmation')
            <div class="premium-error">{{ $message }}</div>
        @enderror
    </div>
</div>

@if ($user->exists)
    <div
        class="premium-alert"
        style="margin-top:18px; border-color:rgba(234,179,8,.25); background:rgba(234,179,8,.08);"
    >
        <strong>Sicherheitshinweis:</strong>
        Wird der Status, die Rolle oder das Passwort geändert, werden bestehende
        Anmeldesitzungen dieses Benutzers aus Sicherheitsgründen beendet.
    </div>
@endif

<div style="display:flex; gap:10px; margin-top:18px; flex-wrap:wrap;">
    <button class="premium-btn gold" type="submit">
        <i class="bi bi-check2-circle"></i>
        Speichern
    </button>

    <a href="{{ route('security.index') }}" class="premium-btn">
        <i class="bi bi-arrow-left"></i>
        Zurück
    </a>
</div>
