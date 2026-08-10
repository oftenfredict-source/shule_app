<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('registration_number')->nullable();
            $table->string('exam_center_number')->nullable();
            $table->string('school_type'); // Primary | Secondary
            $table->string('ownership'); // Public | Private
            $table->string('established_year')->nullable();
            $table->string('region');
            $table->string('district');
            $table->string('ward')->nullable();
            $table->string('email')->nullable();
            $table->string('phone'); // 255XXXXXXXXX
            $table->text('address')->nullable();
            $table->unsignedInteger('number_of_students')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'ready', 'rejected'])->default('pending');
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_registration_requests');
    }
};
