<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->unsignedBigInteger('subject_id');
            $table->string('title');
            $table->string('message')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at', 'dismissed_at'], 'warehouse_notifications_user_unread_idx');
            $table->index(['type', 'subject_id'], 'warehouse_notifications_subject_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_notifications');
    }
};
