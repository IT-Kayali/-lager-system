<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchWithdrawal extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'withdrawal_number',
        'branch_name',
        'quantity',
        'stock_before',
        'stock_after',
        'batch_allocations',
        'note',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'stock_before' => 'decimal:3',
        'stock_after' => 'decimal:3',
        'batch_allocations' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
