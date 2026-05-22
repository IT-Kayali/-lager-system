<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_number',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'whatsapp',
        'street',
        'house_number',
        'postal_code',
        'city',
        'country',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Supplier $supplier): void {
            if (! $supplier->supplier_number) {
                $lastNumber = Supplier::query()
                    ->where('supplier_number', 'like', 'LFR-%')
                    ->orderByDesc('id')
                    ->value('supplier_number');

                $lastSequence = $lastNumber
                    ? (int) str_replace('LFR-', '', $lastNumber)
                    : 1000;

                $supplier->supplier_number = 'LFR-' . ($lastSequence + 1);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function fullAddress(): string
    {
        return collect([
            trim(($this->street ?? '') . ' ' . ($this->house_number ?? '')),
            trim(($this->postal_code ?? '') . ' ' . ($this->city ?? '')),
            $this->country,
        ])->filter()->implode("\n");
    }
}
