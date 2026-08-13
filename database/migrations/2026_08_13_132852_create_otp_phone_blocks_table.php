<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_phone_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('context', 40)->default('school_registration');
            $table->unsignedInteger('otp_send_count')->default(0);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('last_otp_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->unsignedBigInteger('released_by')->nullable();
            $table->string('block_reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['phone', 'context']);
            $table->index(['is_blocked', 'context']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_phone_blocks');
    }
};
