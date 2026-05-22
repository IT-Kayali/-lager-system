<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'batch_number',
        'quantity',
        'storage_location',
        'purchase_price',
        'received_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'purchase_price' => 'decimal:2',
            'received_at' => 'date',
            'expires_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProductBatch $batch): void {
            if (! $batch->batch_number) {
                $batch->batch_number = 'BATCH-' . now()->format('Ymd-His') . '-' . random_int(100, 999);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
