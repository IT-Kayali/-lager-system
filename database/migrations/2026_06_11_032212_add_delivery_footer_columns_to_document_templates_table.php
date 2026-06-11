<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $columns = [
        'delivery_footer_left_text',
        'delivery_footer_middle_text',
        'delivery_footer_right_text',
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
