<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'name',
        'manufacturer_designation',
        'serial_number',
        'unit',
        'supplier',
        'supplier_id',
        'minimum_stock',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'minimum_stock' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (! $product->product_code) {
                $lastCode = Product::query()
                    ->where('product_code', 'like', 'PRD-%')
                    ->orderByDesc('id')
                    ->value('product_code');

                $lastNumber = $lastCode
                    ? (int) str_replace('PRD-', '', $lastCode)
                    : 1000;

                $product->product_code = 'PRD-' . ($lastNumber + 1);
            }
        });

        static::created(function (Product $product): void {
            if (Schema::hasTable('product_price_tiers') && Schema::hasTable('customer_groups')) {
                ProductPriceTier::ensureForProduct($product);
            }
        });
    }



    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('name', $value)->first()
            ?? $this->whereKey($value)->firstOrFail();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'category_product', 'product_id', 'product_category_id')
            ->withTimestamps();
    }

    public function supplierRecord(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class);
    }

    public function offerItems(): HasMany
    {
        return $this->hasMany(OfferItem::class);
    }

    public function getTotalStockAttribute(): float
    {
        return (float) $this->batches()->sum('quantity');
    }

    public function getReservedStockAttribute(): float
    {
        if (! Schema::hasTable('offers') || ! Schema::hasTable('offer_items')) {
            return 0.0;
        }

        return (float) DB::table('offer_items')
            ->join('offers', 'offers.id', '=', 'offer_items.offer_id')
            ->where('offer_items.product_id', $this->id)
            ->whereIn('offers.status', Offer::RESERVING_STATUSES)
            ->sum('offer_items.quantity');
    }

    public function getAvailableStockAttribute(): float
    {
        return max(0, $this->total_stock - $this->reserved_stock);
    }

    public function getMaxReservableAttribute(): float
    {
        return max(0, $this->total_stock - (float) $this->minimum_stock - $this->reserved_stock);
    }

    public function getStockStatusAttribute(): string
    {
        $available = (float) $this->available_stock;
        $minimum = (float) $this->minimum_stock;

        if ($minimum <= 0) {
            return 'ok';
        }

        if ($available <= $minimum) {
            return 'critical';
        }

        if ($available <= ($minimum * 1.10)) {
            return 'low';
        }

        return 'ok';
    }
}
