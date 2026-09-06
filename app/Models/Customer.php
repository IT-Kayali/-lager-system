<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'customer_number',
        'customer_group_id',
        'company_name',
        'email',
        'phone_country_code',
        'phone',
        'city',

        'delivery_street',
        'delivery_house_number',
        'delivery_postal_code',
        'delivery_city',
        'delivery_country',
        'delivery_address',

        'billing_street',
        'billing_house_number',
        'billing_postal_code',
        'billing_city',
        'billing_country',
        'billing_address',

        'vat_number',
        'delivery_note_instruction',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            if (! $customer->customer_number) {
                $lastNumber = Customer::query()
                    ->where('customer_number', 'like', 'KD-%')
                    ->orderByDesc('id')
                    ->value('customer_number');

                $lastNumeric = $lastNumber
                    ? (int) str_replace('KD-', '', $lastNumber)
                    : 10019;

                $customer->customer_number = 'KD-' . ($lastNumeric + 1);
            }
        });
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function walletTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CustomerWalletTransaction::class);
    }

    public function getWalletBalanceAttribute(): float
    {
        return (float) $this->walletTransactions()->sum('amount');
    }

}
