<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    public const WITH_COMPANY = 'with_company';
    public const WITHOUT_COMPANY = 'without_company';

    protected $fillable = [
        'key',
        'name',
        'company_name',
        'company_address',
        'company_street',
        'company_house_number',
        'company_postal_code',
        'company_city',
        'company_country',
        'company_phone',
        'company_email',
        'logo_path',
        'logo_url',
        'payment_info',
        'footer_note',
        'show_company_details',
        'show_logo',
    ];

    protected function casts(): array
    {
        return [
            'show_company_details' => 'boolean',
            'show_logo' => 'boolean',
        ];
    }

    public static function byKey(string $key): self
    {
        return self::query()->where('key', $key)->firstOrFail();
    }
}
