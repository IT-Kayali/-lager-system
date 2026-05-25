<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('application_settings')) {
            Schema::create('application_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('string');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        DB::table('application_settings')->updateOrInsert(
            ['key' => 'reservation_hours'],
            [
                'value' => '72',
                'type' => 'integer',
                'description' => 'Standard-Reservierungsdauer für neue Angebote in Stunden',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('application_settings');
    }
};
