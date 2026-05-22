<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'name',
        'manufacturer',
        'serial_number',
        'unit',
        'supplier',
        'storage_location',
        'minimum_stock',
        'description',
        'image_path',
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

    public function getTotalStockAttribute(): float
    {
        return (float) $this->batches()->sum('quantity');
    }

    public function getReservedStockAttribute(): float
    {
        return 0.0;
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
        $minimumStock = (float) $this->minimum_stock;

        if ($minimumStock <= 0) {
            return 'ok';
        }

        if ($this->available_stock <= ($minimumStock * 0.4)) {
            return 'critical';
        }

        if ($this->available_stock < $minimumStock) {
            return 'low';
        }

        return 'ok';
    }
}
