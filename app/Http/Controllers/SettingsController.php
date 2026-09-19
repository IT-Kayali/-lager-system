<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Models\CustomerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $loginBackgroundPath = ApplicationSetting::loginBackgroundPath();
        $loginLogoPath = ApplicationSetting::loginLogoPath();
        $siteFaviconPath = ApplicationSetting::siteFaviconPath();

        return view('pages.settings.index', [
            'reservationHours' => ApplicationSetting::reservationHours(),
            'offerNumberStart' => ApplicationSetting::offerNumberPattern(),
            'offerNumberPreview' => ApplicationSetting::offerNumberPattern(),
            'lowStockWarningPercentage' => ApplicationSetting::lowStockWarningPercentage(),
            'defaultBatchExpiryMonths' => ApplicationSetting::defaultBatchExpiryMonths(),
            'buttonTheme' => ApplicationSetting::buttonTheme(),
            'siteName' => ApplicationSetting::siteName(),
            'siteFaviconPath' => $siteFaviconPath,
            'siteFaviconUrl' => $siteFaviconPath ? route('site.favicon', [], false) : null,
            'loginBackgroundPath' => $loginBackgroundPath,
            'loginBackgroundUrl' => $loginBackgroundPath ? route('login.background', [], false) : null,
            'loginLogoPath' => $loginLogoPath,
            'loginLogoUrl' => $loginLogoPath ? route('login.logo', [], false) : null,
            'loginEyebrow' => ApplicationSetting::loginEyebrow(),
            'loginTitle' => ApplicationSetting::loginTitle(),
            'loginSubtitle' => ApplicationSetting::loginSubtitle(),
            'customerGroups' => CustomerGroup::query()->withCount('customers')->ordered()->get(),
        ]);
    }

    public function updateReservation(Request $request): RedirectResponse
    {
        $data = $request->validate(['reservation_hours' => ['required', 'integer', 'min:1', 'max:720']]);
        ApplicationSetting::putValue('reservation_hours', $data['reservation_hours'], 'integer', 'Standard-Reservierungsdauer für neue Angebote in Stunden');
        return redirect()->route('settings.index')->with('success', 'Reservierungsdauer wurde gespeichert.');
    }

    public function updateOfferNumber(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'offer_number_start' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]*\d+$/'],
        ], [
            'offer_number_start.regex' => 'Die Angebotsnummer darf Buchstaben, Zahlen, Bindestrich und Unterstrich enthalten und muss mit einer Zahl enden.',
        ]);

        $pattern = strtoupper(trim($data['offer_number_start']));

        ApplicationSetting::putValue(
            'offer_number_start',
            $pattern,
            'string',
            'Startwert und optionales Präfix für fortlaufende Angebotsnummern'
        );

        return redirect()->to(route('settings.index') . '#offer-numbering')
            ->with('success', 'Nummerierung für Angebote wurde gespeichert.');
    }

    public function updateLowStockWarning(Request $request): RedirectResponse
    {
        $data = $request->validate(['low_stock_warning_percentage' => ['required', 'numeric', 'min:0', 'max:1000']]);
        ApplicationSetting::putValue('low_stock_warning_percentage', $data['low_stock_warning_percentage'], 'decimal', 'Prozentualer Zuschlag auf den Mindestbestand für die Warnung Niedriger Bestand');
        return redirect()->to(route('settings.index') . '#low-stock-warning')->with('success', 'Warnschwelle für niedrigen Bestand wurde gespeichert.');
    }

    public function updateBatchExpiry(Request $request): RedirectResponse
    {
        $data = $request->validate(['default_batch_expiry_months' => ['required', 'integer', 'min:1', 'max:240']]);
        ApplicationSetting::putValue('default_batch_expiry_months', $data['default_batch_expiry_months'], 'integer', 'Standard-Ablaufzeit für neu angelegte Chargen in Monaten');
        return redirect()->to(route('settings.index') . '#batch-expiry')->with('success', 'Standard-Ablaufzeit für Chargen wurde gespeichert.');
    }

    public function updateButtonAppearance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'primary_button_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'primary_button_text' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_button_background' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_button_text' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'reset_button_appearance' => ['nullable', 'boolean'],
        ]);
        $theme = $request->boolean('reset_button_appearance') ? ApplicationSetting::buttonThemeDefaults() : [
            'primary_button_background' => strtoupper($data['primary_button_background']), 'primary_button_text' => strtoupper($data['primary_button_text']),
            'secondary_button_background' => strtoupper($data['secondary_button_background']), 'secondary_button_text' => strtoupper($data['secondary_button_text']),
        ];
        $descriptions = ['primary_button_background' => 'Hintergrundfarbe für primäre Standardbuttons', 'primary_button_text' => 'Schriftfarbe für primäre Standardbuttons', 'secondary_button_background' => 'Hintergrundfarbe für sekundäre Standardbuttons', 'secondary_button_text' => 'Schriftfarbe für sekundäre Standardbuttons'];
        foreach ($theme as $key => $value) ApplicationSetting::putValue($key, $value, 'color', $descriptions[$key]);
        return redirect()->to(route('settings.index') . '#button-appearance')->with('success', $request->boolean('reset_button_appearance') ? 'Die Standardfarben der Buttons wurden wiederhergestellt.' : 'Das Button-Design wurde gespeichert.');
    }

    public function updateLoginAppearance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:80'], 'login_eyebrow' => ['required', 'string', 'max:80'], 'login_title' => ['required', 'string', 'max:120'], 'login_subtitle' => ['required', 'string', 'max:240'],
            'login_background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], 'login_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'site_favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'remove_login_background' => ['nullable', 'boolean'], 'remove_login_logo' => ['nullable', 'boolean'], 'remove_site_favicon' => ['nullable', 'boolean'],
        ]);
        ApplicationSetting::putValue('site_name', trim($data['site_name']), 'string', 'Name der Anwendung im Browser-Tab');
        ApplicationSetting::putValue('login_eyebrow', trim($data['login_eyebrow']), 'string', 'Kleine Überschrift der Anmeldeseite');
        ApplicationSetting::putValue('login_title', trim($data['login_title']), 'string', 'Hauptüberschrift der Anmeldeseite');
        ApplicationSetting::putValue('login_subtitle', trim($data['login_subtitle']), 'string', 'Beschreibungstext der Anmeldeseite');
        $this->updateStoredImage($request, 'login_background', 'remove_login_background', ApplicationSetting::loginBackgroundPath(), 'login-backgrounds', 'login_background_path', 'Hintergrundbild der Anmeldeseite');
        $this->updateStoredImage($request, 'login_logo', 'remove_login_logo', ApplicationSetting::loginLogoPath(), 'login-logos', 'login_logo_path', 'Logo der Anmeldeseite');
        $this->updateStoredImage($request, 'site_favicon', 'remove_site_favicon', ApplicationSetting::siteFaviconPath(), 'site-favicons', 'site_favicon_path', 'Favicon der Anwendung');
        return redirect()->to(route('settings.index') . '#login-appearance')->with('success', 'Login- und Browser-Einstellungen wurden gespeichert.');
    }

    public function applicationThemeCss(): Response
    {
        $theme = ApplicationSetting::buttonTheme();

        $css = sprintf(
            ":root {\n    --premium-primary-button-bg: %s;\n    --premium-primary-button-text: %s;\n    --premium-secondary-button-bg: %s;\n    --premium-secondary-button-text: %s;\n}\n",
            $theme['primary_button_background'],
            $theme['primary_button_text'],
            $theme['secondary_button_background'],
            $theme['secondary_button_text'],
        );

        return response($css, 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function loginThemeCss(): Response
    {
        $background = ApplicationSetting::loginBackgroundPath()
            ? sprintf(
                "linear-gradient(135deg, rgba(16, 14, 10, .78), rgba(16, 14, 10, .30) 42%%, rgba(255, 244, 221, .68)), url('%s')",
                route('login.background', [], false),
            )
            : "linear-gradient(135deg, rgba(16, 14, 10, .78), rgba(16, 14, 10, .30) 42%, rgba(255, 244, 221, .68)), radial-gradient(circle at 50% 20%, rgba(239, 202, 86, .35), transparent 30%), linear-gradient(135deg, #1d1710, #5b4314 46%, #fff2d4)";

        return response(
            ".login-page {\n    background: {$background};\n}\n",
            200,
            [
                'Content-Type' => 'text/css; charset=UTF-8',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    public function loginBackground() { $path = ApplicationSetting::loginBackgroundPath(); abort_unless($path && Storage::disk('public')->exists($path), 404); return Storage::disk('public')->response($path); }
    public function loginLogo() { $path = ApplicationSetting::loginLogoPath(); abort_unless($path && Storage::disk('public')->exists($path), 404); return Storage::disk('public')->response($path); }
    public function siteFavicon() { $path = ApplicationSetting::siteFaviconPath(); abort_unless($path && Storage::disk('public')->exists($path), 404); return Storage::disk('public')->response($path); }

    private function updateStoredImage(Request $request, string $fileField, string $removeField, ?string $currentPath, string $directory, string $settingKey, string $description): void
    {
        if ($request->boolean($removeField)) { if ($currentPath) Storage::disk('public')->delete($currentPath); ApplicationSetting::putValue($settingKey, '', 'string', $description); return; }
        if (! $request->hasFile($fileField)) return;
        $newPath = $request->file($fileField)->store($directory, 'public');
        if ($currentPath) Storage::disk('public')->delete($currentPath);
        ApplicationSetting::putValue($settingKey, $newPath, 'string', $description);
    }
}