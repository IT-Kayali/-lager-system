<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchWithdrawalItem extends Model
{
    protected $fillable = [
        'branch_withdrawal_id',
        'product_id',
        'quantity',
        'stock_before',
        'stock_after',
        'batch_allocations',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'stock_before' => 'decimal:3',
            'stock_after' => 'decimal:3',
            'batch_allocations' => 'array',
        ];
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(BranchWithdrawal::class, 'branch_withdrawal_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
