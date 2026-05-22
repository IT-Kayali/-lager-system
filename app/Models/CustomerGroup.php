<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    public const GOLD = 'gold';
    public const SILVER = 'silver';
    public const DIAMOND = 'diamond';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
