@csrf

@if (session('error'))
    <div class="premium-alert" style="border-color: rgba(239,68,68,.25); background: rgba(239,68,68,.10); color:#991b1b;">
        {{ session('error') }}
    </div>
@endif

<div class="premium-form-grid">
    <div class="premium-form-field">
        <label for="name">Name *</label>
        <input id="name" name="name" class="premium-input" value="{{ old('name', $user->name) }}" required>
        @error('name') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="email">E-Mail *</label>
        <input id="email" name="email" type="email" class="premium-input" value="{{ old('email', $user->email) }}" required>
        @error('email') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="role">Rolle *</label>
        <select id="role" name="role" class="premium-select" required>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('role') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field" style="display:flex; align-items:end;">
        <label style="display:flex; gap:10px; align-items:center; font-weight:800;">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
            Benutzer aktiv
        </label>
        @error('is_active') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="password">Passwort {{ $user->exists ? 'optional' : '*' }}</label>
        <input id="password" name="password" type="password" class="premium-input" autocomplete="new-password">
        @error('password') <div class="premium-error">{{ $message }}</div> @enderror
    </div>

    <div class="premium-form-field">
        <label for="password_confirmation">Passwort bestätigen</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="premium-input" autocomplete="new-password">
    </div>
</div>

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
