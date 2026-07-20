<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationSetting extends Model
{
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

    public static function loginBackgroundPath(): ?string
    {
        $path = trim((string) static::getValue('login_background_path', ''));

        return $path !== '' ? $path : null;
    }
}
