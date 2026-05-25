<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerWalletTransaction extends Model
{
    public const TYPE_OFFER_DEBIT = 'offer_debit';
    public const TYPE_OFFER_PAYMENT_CREDIT = 'offer_payment_credit';
    public const TYPE_OFFER_CANCEL_ADJUSTMENT = 'offer_cancel_adjustment';
    public const TYPE_MANUAL_CREDIT = 'manual_credit';
    public const TYPE_MANUAL_DEBIT = 'manual_debit';

    protected $fillable = [
        'customer_id',
        'offer_id',
        'user_id',
        'type',
        'amount',
        'balance_after',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
