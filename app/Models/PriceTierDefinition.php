<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceTierDefinition extends Model
{
    protected $fillable = [
        'key',
        'label',
        'min_grams',
        'max_grams',
    ];

    protected function casts(): array
    {
        return [
            'min_grams' => 'integer',
            'max_grams' => 'integer',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('min_grams')
            ->orderBy('max_grams')
            ->orderBy('id');
    }

    public function productPriceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class, 'tier_key', 'key');
    }
}
