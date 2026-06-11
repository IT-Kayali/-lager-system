<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'delivery_title',
        'delivery_date_label',
        'delivery_customer_number_label',
        'delivery_order_number_label',
        'delivery_shipping_method_label',
        'delivery_shipping_method_text',
        'delivery_intro_text',
        'delivery_quantity_label',
        'delivery_product_label',
        'delivery_footer_text',
    ];

    public function up(): void
    {
        $existing = Schema::getColumnListing('document_templates');

        Schema::table('document_templates', function (Blueprint $table) use ($existing) {
            foreach ($this->columns as $column) {
                if (! in_array($column, $existing, true)) {
                    $table->text($column)->nullable();
                }
            }
        });

        DB::table('document_templates')
            ->where('show_logo', true)
            ->update([
                'delivery_title' => 'Lieferschein',
                'delivery_date_label' => 'Datum:',
                'delivery_customer_number_label' => 'Kunden-Nr.:',
                'delivery_order_number_label' => 'Bestell-Nr.:',
                'delivery_shipping_method_label' => 'Versandart:',
                'delivery_shipping_method_text' => 'Lieferung oder Abholung',
                'delivery_intro_text' => "Sehr geehrte Damen und Herren,\n\nvielen Dank für Ihre Bestellung. Wir liefern Ihnen wie vereinbart folgende Waren:",
                'delivery_quantity_label' => 'Menge',
                'delivery_product_label' => 'Bezeichnung',
                'delivery_footer_text' => 'Die gelieferte Ware bleibt bis zur vollständigen Bezahlung unser Eigentum.',
            ]);

        DB::table('document_templates')
            ->where('show_logo', false)
            ->update([
                'delivery_title' => 'Delivery Notice',
                'delivery_date_label' => 'Date:',
                'delivery_customer_number_label' => 'Customer Nr.:',
                'delivery_order_number_label' => 'Order Nr.:',
                'delivery_shipping_method_label' => 'Shipping Method:',
                'delivery_shipping_method_text' => 'Lieferung oder Abholung',
                'delivery_intro_text' => '',
                'delivery_quantity_label' => 'Quantity',
                'delivery_product_label' => 'Product',
                'delivery_footer_text' => '',
            ]);
    }

    public function down(): void
    {
        $existing = Schema::getColumnListing('document_templates');
        $drop = array_values(array_filter($this->columns, fn ($column) => in_array($column, $existing, true)));

        if (! empty($drop)) {
            Schema::table('document_templates', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }
};
