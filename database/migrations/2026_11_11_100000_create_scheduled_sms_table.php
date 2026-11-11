<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('scheduled_sms')) {
            return;
        }

        Schema::create('scheduled_sms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->string('recipient_type', 60);
            $table->text('message');
            $table->json('selected_ids')->nullable();
            $table->unsignedBigInteger('classID')->nullable();
            $table->timestamp('scheduled_at');
            $table->string('status', 20)->default('pending'); // pending, processing, sent, failed, cancelled
            $table->text('result_summary')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_name')->nullable();
            $table->timestamps();

            $table->index(['schoolID', 'status']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_sms');
    }
};
