<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fingerprint_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schoolID')->constrained('schools', 'schoolID')->onDelete('cascade');
            $table->string('label', 120);
            $table->string('brand', 60)->default('ZKTeco');
            $table->string('ip', 45);
            $table->unsignedSmallInteger('port')->default(4370);
            $table->string('comm_key', 16)->default('0');
            $table->string('usage_type', 40)->nullable();
            $table->string('location', 150)->nullable();
            $table->string('serial_number', 80)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['schoolID', 'status']);
            $table->unique(['schoolID', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fingerprint_devices');
    }
};
