<?php

use App\Concerns\PasswordValidationRules;
use App\Services\UserSessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
/* @chisel-passkeys */
use Laravel\Passkeys\Actions\DeletePasskey;
use Livewire\Attributes\Locked;
/* @end-chisel-passkeys */
/* @chisel-2fa */
use Livewire\Attributes\On;
/* @end-chisel-2fa */

new
#[Layout('components.layouts.premium', [
    'title' => 'Mein Konto & Sicherheit',
    'subtitle' => 'Passwort und persönliche Sicherheitseinstellungen verwalten.',
])]
#[Title('Mein Konto & Sicherheit')]
class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /* @chisel-2fa */
    public bool $canManageTwoFactor;

    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;
    /* @end-chisel-2fa */

    /* @chisel-passkeys */
    #[Locked]
    public bool $canManagePasskeys;

    #[Locked]
    public array $passkeys = [];

    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $deletingPasskeyId = null;

    #[Locked]
    public string $deletingPasskeyName = '';
    /* @end-chisel-passkeys */

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        /* @chisel-2fa */
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }
        /* @end-chisel-2fa */

        /* @chisel-passkeys */
        $this->canManagePasskeys = Features::canManagePasskeys();

        if ($this->canManagePasskeys) {
            $this->loadPasskeys();
        }
        /* @end-chisel-passkeys */
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(UserSessionService $userSessions): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        $user = Auth::user();

        $user->update([
            'password' => $validated['password'],
        ]);

        $userSessions->revoke($user);

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->redirect(route('login'), navigate: true);
    }

    /* @chisel-passkeys */
    /**
     * Load the user's passkeys.
     */
    public function loadPasskeys(): void
    {
        $this->passkeys = auth()->user()->passkeys()
            ->select(['id', 'name', 'credential', 'created_at', 'last_used_at'])
            ->latest()
            ->get()
            ->map(fn ($passkey) => [
                'id' => $passkey->id,
                'name' => $passkey->name,
                'authenticator' => $passkey->authenticator,
                'created_at_diff' => $passkey->created_at->diffForHumans(),
                'last_used_at_diff' => $passkey->last_used_at?->diffForHumans(),
            ])
            ->toArray();
    }

    /**
     * Show the delete confirmation modal.
     */
    public function confirmDelete(int $passkeyId): void
    {
        $passkey = auth()->user()->passkeys()->findOrFail($passkeyId);

        $this->deletingPasskeyId = $passkey->id;
        $this->deletingPasskeyName = $passkey->name;
        $this->showDeleteModal = true;
    }

    /**
     * Delete the passkey.
     */
    public function deletePasskey(DeletePasskey $deletePasskey): void
    {
        if (! $this->deletingPasskeyId) {
            return;
        }

        $passkey = auth()->user()->passkeys()->findOrFail($this->deletingPasskeyId);

        $deletePasskey(auth()->user(), $passkey);

        $this->closeDeleteModal();
        $this->loadPasskeys();
    }

    /**
     * Close the delete confirmation modal.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPasskeyId = null;
        $this->deletingPasskeyName = '';
    }
    /* @end-chisel-passkeys */

    /* @chisel-2fa */
    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }
    /* @end-chisel-2fa */
}; ?>


<section class="account-security-page">
    <div class="premium-card account-security-card">
        <div class="account-security-card-head">
            <div class="account-security-icon">
                <i class="bi bi-shield-lock"></i>
            </div>

            <div>
                <p class="account-security-kicker">Persönliche Sicherheit</p>
                <h2>Passwort ändern</h2>
                <p>
                    Verwende ein langes, einzigartiges Passwort. Nach einer erfolgreichen Änderung
                    werden bestehende Sitzungen beendet und du meldest dich anschließend neu an.
                </p>
            </div>
        </div>

        <form wire:submit="updatePassword" class="account-security-form">
            <div class="premium-form-field">
                <label for="current_password">Aktuelles Passwort <span aria-hidden="true">*</span></label>
                <div class="account-password-field">
                    <input
                        id="current_password"
                        wire:model="current_password"
                        class="premium-input @error('current_password') premium-invalid-field @enderror"
                        type="password"
                        required
                        autocomplete="current-password"
                    >
                    <i class="bi bi-key"></i>
                </div>
                @error('current_password')
                    <p class="account-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="account-security-grid">
                <div class="premium-form-field">
                    <label for="password">Neues Passwort <span aria-hidden="true">*</span></label>
                    <div class="account-password-field">
                        <input
                            id="password"
                            wire:model="password"
                            class="premium-input @error('password') premium-invalid-field @enderror"
                            type="password"
                            required
                            autocomplete="new-password"
                            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                        >
                        <i class="bi bi-lock"></i>
                    </div>
                    @error('password')
                        <p class="account-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="premium-form-field">
                    <label for="password_confirmation">Passwort bestätigen <span aria-hidden="true">*</span></label>
                    <div class="account-password-field">
                        <input
                            id="password_confirmation"
                            wire:model="password_confirmation"
                            class="premium-input"
                            type="password"
                            required
                            autocomplete="new-password"
                            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                        >
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>

            <div class="account-security-actions">
                <button
                    type="submit"
                    class="premium-btn gold"
                    data-test="update-password-button"
                    wire:loading.attr="disabled"
                    wire:target="updatePassword"
                >
                    <i class="bi bi-key-fill"></i>
                    <span wire:loading.remove wire:target="updatePassword">Passwort ändern</span>
                    <span wire:loading wire:target="updatePassword">Wird gespeichert …</span>
                </button>
            </div>
        </form>
    </div>

    <aside class="premium-card account-security-info">
        <div class="account-security-info-head">
            <span><i class="bi bi-shield-check"></i></span>
            <div>
                <h2>Sicherheitsstatus</h2>
                <p>Schutzmaßnahmen für dein Benutzerkonto.</p>
            </div>
        </div>

        <div class="account-security-status-list">
            <div class="account-security-status">
                <span class="account-security-status-icon success">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <div>
                    <strong>HTTPS aktiv</strong>
                    <small>Die Verbindung zum Lagerverwaltungssystem ist verschlüsselt.</small>
                </div>
            </div>

            <div class="account-security-status">
                <span class="account-security-status-icon success">
                    <i class="bi bi-person-check-fill"></i>
                </span>
                <div>
                    <strong>Sichere Sitzungen</strong>
                    <small>Passwort- und Kontostatusänderungen widerrufen bestehende Sitzungen.</small>
                </div>
            </div>

            <div class="account-security-status">
                <span class="account-security-status-icon neutral">
                    <i class="bi bi-phone"></i>
                </span>
                <div>
                    <strong>Zwei-Faktor-Authentifizierung</strong>
                    <small>Wird in einer späteren Security-Phase kontrolliert aktiviert und getestet.</small>
                </div>
            </div>
        </div>
    </aside>

    {{-- @chisel-2fa --}}
    @if ($canManageTwoFactor)
        <section class="premium-card account-security-optional">
            <div class="account-security-card-head compact">
                <div class="account-security-icon">
                    <i class="bi bi-phone"></i>
                </div>

                <div>
                    <p class="account-security-kicker">Zusätzlicher Schutz</p>
                    <h2>Zwei-Faktor-Authentifizierung</h2>
                    <p>Verwalte die Zwei-Faktor-Authentifizierung für dein persönliches Konto.</p>
                </div>
            </div>

            @if ($twoFactorEnabled)
                <div class="account-security-optional-body">
                    <p>2FA ist für dieses Konto aktiviert.</p>

                    <button type="button" class="premium-btn premium-danger" wire:click="disable">
                        <i class="bi bi-shield-x"></i>
                        2FA deaktivieren
                    </button>

                    <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                </div>
            @else
                <div class="account-security-optional-body">
                    <p>
                        Nach der Aktivierung wird beim Login zusätzlich ein zeitbasierter Sicherheitscode
                        aus einer TOTP-kompatiblen App benötigt.
                    </p>

                    <flux:modal.trigger name="two-factor-setup-modal">
                        <button
                            type="button"
                            class="premium-btn gold"
                            wire:click="$dispatch('start-two-factor-setup')"
                        >
                            <i class="bi bi-shield-plus"></i>
                            2FA aktivieren
                        </button>
                    </flux:modal.trigger>

                    <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                </div>
            @endif
        </section>
    @endif
    {{-- @end-chisel-2fa --}}

    {{-- @chisel-passkeys --}}
    @if ($canManagePasskeys)
        <section class="premium-card account-security-optional">
            <div class="account-security-card-head compact">
                <div class="account-security-icon">
                    <i class="bi bi-key"></i>
                </div>

                <div>
                    <p class="account-security-kicker">Passwortlos anmelden</p>
                    <h2>Passkeys</h2>
                    <p>Verwalte vorhandene Passkeys für dein Konto.</p>
                </div>
            </div>

            <div class="account-security-passkeys">
                @forelse ($passkeys as $passkey)
                    <div class="account-security-passkey">
                        <div>
                            <strong>{{ $passkey['name'] }}</strong>
                            <small>
                                Hinzugefügt {{ $passkey['created_at_diff'] }}
                                @if ($passkey['last_used_at_diff'])
                                    · zuletzt verwendet {{ $passkey['last_used_at_diff'] }}
                                @endif
                            </small>
                        </div>

                        <button
                            type="button"
                            class="premium-icon-btn premium-danger"
                            wire:click="confirmDelete({{ $passkey['id'] }})"
                            aria-label="Passkey entfernen"
                        >
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                @empty
                    <div class="account-security-empty">Noch keine Passkeys vorhanden.</div>
                @endforelse

                <x-passkey-registration />
            </div>
        </section>

        <flux:modal
            name="delete-passkey-modal"
            class="max-w-md md:min-w-md"
            @close="closeDeleteModal"
            wire:model="showDeleteModal"
        >
            <div class="space-y-6">
                <div class="space-y-2">
                    <flux:heading size="lg">Passkey entfernen</flux:heading>
                    <flux:text>
                        Soll der Passkey „{{ $deletingPasskeyName }}“ wirklich entfernt werden?
                    </flux:text>
                </div>

                <div class="flex gap-3 justify-end">
                    <flux:button variant="outline" wire:click="closeDeleteModal">
                        Abbrechen
                    </flux:button>
                    <flux:button variant="danger" wire:click="deletePasskey">
                        Entfernen
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
    {{-- @end-chisel-passkeys --}}
</section>

<style>
    .account-security-page {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, .75fr);
        gap: 22px;
        align-items: start;
    }

    .account-security-card,
    .account-security-info,
    .account-security-optional {
        padding: 26px;
    }

    .account-security-card-head {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        padding-bottom: 22px;
        margin-bottom: 22px;
        border-bottom: 1px solid #e7dece;
    }

    .account-security-card-head.compact {
        margin-bottom: 18px;
    }

    .account-security-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: #f3e8be;
        color: #8a6a00;
        border: 1px solid #dfc96f;
        font-size: 21px;
    }

    .account-security-kicker {
        margin: 0 0 5px;
        color: #8a6a00;
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .account-security-card-head h2,
    .account-security-info-head h2 {
        margin: 0;
        color: #111;
        font-size: 22px;
        font-weight: 950;
    }

    .account-security-card-head p:not(.account-security-kicker),
    .account-security-info-head p {
        margin: 6px 0 0;
        color: #665f54;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.55;
    }

    .account-security-form {
        display: grid;
        gap: 18px;
    }

    .account-security-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .premium-form-field {
        display: grid;
        gap: 8px;
    }

    .premium-form-field label {
        color: #211d17;
        font-size: 13px;
        font-weight: 950;
    }

    .premium-form-field label span {
        color: #b91c1c;
    }

    .account-password-field {
        position: relative;
    }

    .account-password-field .premium-input {
        width: 100%;
        min-height: 48px;
        padding: 11px 44px 11px 14px !important;
        background: #fffdf8 !important;
        color: #111 !important;
        font-weight: 750;
    }

    .account-password-field > i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #8a806f;
        pointer-events: none;
    }

    .account-field-error {
        margin: 0;
        color: #b91c1c;
        font-size: 12px;
        font-weight: 800;
    }

    .account-security-actions {
        display: flex;
        justify-content: flex-start;
        padding-top: 2px;
    }

    .account-security-actions .premium-btn {
        padding: 0 18px;
        cursor: pointer;
    }

    .account-security-actions .premium-btn:disabled {
        opacity: .65;
        cursor: wait;
    }

    .account-security-info {
        position: sticky;
        top: 24px;
    }

    .account-security-info-head {
        display: flex;
        align-items: center;
        gap: 13px;
        padding-bottom: 18px;
        border-bottom: 1px solid #e7dece;
    }

    .account-security-info-head > span {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: #171716;
        color: #ffe690;
        font-size: 18px;
    }

    .account-security-status-list {
        display: grid;
        gap: 12px;
        margin-top: 18px;
    }

    .account-security-status {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr);
        gap: 12px;
        align-items: start;
        padding: 14px;
        border: 1px solid #e7dece;
        border-radius: 14px;
        background: #fffdf8;
    }

    .account-security-status-icon {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 11px;
    }

    .account-security-status-icon.success {
        background: #dcfce7;
        color: #166534;
    }

    .account-security-status-icon.neutral {
        background: #f3e8be;
        color: #8a6a00;
    }

    .account-security-status strong,
    .account-security-passkey strong {
        display: block;
        color: #111;
        font-size: 13px;
        font-weight: 950;
    }

    .account-security-status small,
    .account-security-passkey small {
        display: block;
        margin-top: 4px;
        color: #665f54;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.45;
    }

    .account-security-optional {
        grid-column: 1 / -1;
    }

    .account-security-optional-body {
        display: grid;
        justify-items: start;
        gap: 16px;
        color: #665f54;
        font-weight: 700;
    }

    .account-security-passkeys {
        display: grid;
        gap: 12px;
    }

    .account-security-passkey {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        padding: 14px;
        border: 1px solid #e7dece;
        border-radius: 14px;
        background: #fffdf8;
    }

    .account-security-empty {
        padding: 22px;
        text-align: center;
        color: #665f54;
        border: 1px dashed #d8cbb7;
        border-radius: 14px;
        background: #fffdf8;
        font-weight: 750;
    }

    @media (max-width: 1050px) {
        .account-security-page {
            grid-template-columns: 1fr;
        }

        .account-security-info {
            position: static;
        }
    }

    @media (max-width: 700px) {
        .account-security-card,
        .account-security-info,
        .account-security-optional {
            padding: 18px;
        }

        .account-security-grid {
            grid-template-columns: 1fr;
        }

        .account-security-card-head {
            display: grid;
        }
    }
</style>
