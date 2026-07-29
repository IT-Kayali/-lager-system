<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    public const GOLD = 'gold';
    public const SILVER = 'silver';
    public const DIAMOND = 'diamond';

    public const DEFAULT_COLORS = [
        self::GOLD => '#D4AD16',
        self::SILVER => '#9CA3AF',
        self::DIAMOND => '#2563EB',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE slug WHEN 'gold' THEN 1 WHEN 'silver' THEN 2 WHEN 'diamond' THEN 3 ELSE 4 END")
            ->orderBy('name');
    }

    public function displayColor(): string
    {
        $color = strtoupper((string) $this->color);

        if (preg_match('/^#[0-9A-F]{6}$/', $color) === 1) {
            return $color;
        }

        return self::DEFAULT_COLORS[$this->slug] ?? '#475569';
    }

    public function textColor(): string
    {
        $hex = ltrim($this->displayColor(), '#');
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luminance >= 150 ? '#111827' : '#FFFFFF';
    }

    public function badgeStyle(): string
    {
        $color = $this->displayColor();

        return "background-color: {$color}; color: {$this->textColor()}; border-color: {$color};";
    }
}
