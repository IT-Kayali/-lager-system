<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationSetting extends Model
{
    public const DEFAULT_BUTTON_THEME = [
        'primary_button_background' => '#D4AA20',
        'primary_button_text' => '#171716',
        'secondary_button_background' => '#111111',
        'secondary_button_text' => '#FFFFFF',
    ];

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }

    public static function putValue(string $key, mixed $value, string $type = 'string', ?string $description = null): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    public static function reservationHours(): int
    {
        $hours = (int) static::getValue('reservation_hours', 72);

        return max(1, min(720, $hours));
    }

    public static function lowStockWarningPercentage(): float
    {
        $percentage = (float) static::getValue('low_stock_warning_percentage', 10);

        return max(0, min(1000, $percentage));
    }

    public static function defaultBatchExpiryMonths(): int
    {
        $months = (int) static::getValue('default_batch_expiry_months', 24);

        return max(1, min(240, $months));
    }

    public static function buttonThemeDefaults(): array
    {
        return self::DEFAULT_BUTTON_THEME;
    }

    public static function buttonTheme(): array
    {
        $stored = static::query()
            ->whereIn('key', array_keys(self::DEFAULT_BUTTON_THEME))
            ->pluck('value', 'key');

        $theme = [];

        foreach (self::DEFAULT_BUTTON_THEME as $key => $default) {
            $theme[$key] = static::normalizeHexColor($stored->get($key), $default);
        }

        return $theme;
    }

    public static function siteName(): string
    {
        $fallback = (string) config('app.name', 'Lagerverwaltung');

        return trim((string) static::getValue('site_name', $fallback)) ?: $fallback;
    }

    public static function siteFaviconPath(): ?string
    {
        $path = trim((string) static::getValue('site_favicon_path', ''));

        return $path !== '' ? $path : null;
    }

    public static function loginBackgroundPath(): ?string
    {
        $path = trim((string) static::getValue('login_background_path', ''));

        return $path !== '' ? $path : null;
    }

    public static function loginLogoPath(): ?string
    {
        $path = trim((string) static::getValue('login_logo_path', ''));

        return $path !== '' ? $path : null;
    }

    public static function loginEyebrow(): string
    {
        return trim((string) static::getValue(
            'login_eyebrow',
            'Sicherer Zugriff'
        )) ?: 'Sicherer Zugriff';
    }

    public static function loginTitle(): string
    {
        return trim((string) static::getValue(
            'login_title',
            'Alles im Lager sofort im Blick.'
        )) ?: 'Alles im Lager sofort im Blick.';
    }

    public static function loginSubtitle(): string
    {
        return trim((string) static::getValue(
            'login_subtitle',
            'Modernes Dashboard für Bestände, Angebote, Rechnungen und Warnungen — schnell, klar und sicher.'
        )) ?: 'Modernes Dashboard für Bestände, Angebote, Rechnungen und Warnungen — schnell, klar und sicher.';
    }

    private static function normalizeHexColor(mixed $value, string $default): string
    {
        $color = strtoupper(trim((string) $value));

        return preg_match('/^#[0-9A-F]{6}$/', $color) === 1
            ? $color
            : strtoupper($default);
    }
}
