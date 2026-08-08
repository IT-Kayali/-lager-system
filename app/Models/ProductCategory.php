<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    protected $fillable = [
        'name',
        'priority',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductCategory $category): void {
            if (! $category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function getRouteName(): string
    {
        return preg_replace('/\s+/', '-', trim((string) $this->name));
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $nameFromUrl = str_replace('-', ' ', (string) $value);

        return $this->where('name', $value)->first()
            ?? $this->where('name', $nameFromUrl)->first()
            ?? $this->whereKey($value)->firstOrFail();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product', 'product_category_id', 'product_id')
            ->withTimestamps();
    }
}
