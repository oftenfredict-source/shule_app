<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fingerprint_attendance_setups')) {
            return;
        }

        Schema::create('fingerprint_attendance_setups', function (Blueprint $table) {
            $table->id('setupID');
            $table->string('title', 200);
            $table->string('version', 50)->nullable();
            $table->text('description')->nullable();
            $table->enum('environment', ['local', 'subdomain', 'main_domain', 'live_cpanel'])->default('local');
            $table->string('file_path', 500);
            $table->string('original_filename', 255);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index(['environment', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprint_attendance_setups');
    }
};
