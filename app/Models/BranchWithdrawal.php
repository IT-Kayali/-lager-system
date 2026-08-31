<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BranchWithdrawal extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_CANCELLED = 'cancelled';

    public const RESERVING_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
    ];

    public const BRANCH_MAIN = 'Hauptfiliale';
    public const BRANCH_DEZ = 'DEZ Filiale';

    protected $fillable = [
        'product_id',
        'user_id',
        'processed_by',
        'withdrawal_number',
        'branch_name',
        'status',
        'processed_at',
        'quantity',
        'stock_before',
        'stock_after',
        'batch_allocations',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'stock_before' => 'decimal:3',
            'stock_after' => 'decimal:3',
            'batch_allocations' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public static function branches(): array
    {
        return [
            self::BRANCH_MAIN => self::BRANCH_MAIN,
            self::BRANCH_DEZ => self::BRANCH_DEZ,
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_OPEN => 'Offen',
            self::STATUS_IN_PROGRESS => 'In Bearbeitung',
            self::STATUS_ISSUED => 'Ausgegeben',
            self::STATUS_CANCELLED => 'Storniert',
        ];
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BranchWithdrawalItem::class)
            ->orderBy('id');
    }
}
