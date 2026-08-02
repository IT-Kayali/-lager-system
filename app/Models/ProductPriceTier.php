<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ProductPriceTier extends Model
{
    public const TIERS = [
        '50g' => [
            'label' => '50g',
            'min_grams' => 50,
            'max_grams' => 90,
        ],
        '100g' => [
            'label' => '100g',
            'min_grams' => 91,
            'max_grams' => 239,
        ],
        '250g' => [
            'label' => '250g',
            'min_grams' => 240,
            'max_grams' => 460,
        ],
        '500g' => [
            'label' => '500g',
            'min_grams' => 461,
            'max_grams' => 750,
        ],
        '1000g' => [
            'label' => '1000g',
            'min_grams' => 751,
            'max_grams' => 5000,
        ],
    ];

    protected $fillable = [
        'product_id',
        'customer_group_id',
        'tier_key',
        'tier_label',
        'min_grams',
        'max_grams',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'min_grams' => 'integer',
            'max_grams' => 'integer',
        ];
    }

    public static function definitions(): Collection
    {
        if (Schema::hasTable('price_tier_definitions')) {
            $definitions = PriceTierDefinition::query()->ordered()->get();

            if ($definitions->isNotEmpty()) {
                return $definitions;
            }
        }

        return collect(self::TIERS)
            ->map(fn (array $tier, string $key) => (object) [
                'key' => $key,
                'label' => $tier['label'],
                'min_grams' => $tier['min_grams'],
                'max_grams' => $tier['max_grams'],
            ])
            ->values();
    }

    public static function definitionForKey(string $key): ?object
    {
        return self::definitions()->first(
            fn (object $definition) => $definition->key === $key
        );
    }

    public static function ensureForProduct(Product $product): void
    {
        $groups = CustomerGroup::query()->get();
        $definitions = self::definitions();

        foreach ($groups as $group) {
            foreach ($definitions as $definition) {
                $tier = self::query()->firstOrNew([
                    'product_id' => $product->id,
                    'customer_group_id' => $group->id,
                    'tier_key' => $definition->key,
                ]);

                if (! $tier->exists) {
                    $tier->price = 0;
                }

                $tier->fill([
                    'tier_label' => $definition->label,
                    'min_grams' => $definition->min_grams,
                    'max_grams' => $definition->max_grams,
                ])->save();
            }
        }
    }

    public static function synchronizeAll(): void
    {
        Product::query()
            ->select('id')
            ->cursor()
            ->each(fn (Product $product) => self::ensureForProduct($product));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
