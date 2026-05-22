<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public static function ensureForProduct(Product $product): void
    {
        $groups = CustomerGroup::query()->get();

        foreach ($groups as $group) {
            foreach (self::TIERS as $key => $tier) {
                self::query()->firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'customer_group_id' => $group->id,
                        'tier_key' => $key,
                    ],
                    [
                        'tier_label' => $tier['label'],
                        'min_grams' => $tier['min_grams'],
                        'max_grams' => $tier['max_grams'],
                        'price' => 0,
                    ]
                );
            }
        }
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
