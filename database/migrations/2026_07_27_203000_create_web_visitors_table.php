<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('page_url', 500)->nullable();
            $table->string('page_name')->nullable()->index();
            $table->string('route_name')->nullable()->index();
            $table->string('referer', 500)->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('visited_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_visitors');
    }
};
