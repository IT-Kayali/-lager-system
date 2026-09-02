<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferInternalNote extends Model
{
    protected $fillable = [
        'offer_id',
        'user_id',
        'note',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
