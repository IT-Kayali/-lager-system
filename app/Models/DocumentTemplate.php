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
        'company_website',
        'company_vat_id',
        'logo_path',
        'logo_url',
        'product_column_label',
        'tax_rate',
        'background_image_path',
        'delivery_background_image_path',
        'delivery_logo_path',
        'payment_info',
        'footer_note',
        'delivery_footer_text',
        'delivery_footer_right_text',
        'delivery_footer_middle_text',
        'delivery_footer_left_text',
        'delivery_product_label',
        'delivery_quantity_label',
        'delivery_intro_text',
        'delivery_shipping_method_text',
        'delivery_shipping_method_label',
        'delivery_order_number_label',
        'delivery_customer_number_label',
        'delivery_date_label',
        'delivery_title',
        'show_company_details',
        'show_logo',
    ];

    protected function casts(): array
    {
        return [
            'show_company_details' => 'boolean',
            'show_logo' => 'boolean',
            'tax_rate' => 'decimal:2',
        ];
    }

    public static function byKey(string $key): self
    {
        return self::query()->where('key', $key)->firstOrFail();
    }
}
