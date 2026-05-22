<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferItem extends Model
{
    protected $fillable = [
        'offer_id',
        'product_id',
        'product_price_tier_id',
        'product_code',
        'product_name',
        'quantity',
        'unit',
        'tier_key',
        'tier_label',
        'unit_price',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceTier(): BelongsTo
    {
        return $this->belongsTo(ProductPriceTier::class, 'product_price_tier_id');
    }
}
