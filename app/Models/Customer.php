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
        'phone',
        'city',
        'delivery_address',
        'billing_address',
        'vat_number',
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
}
