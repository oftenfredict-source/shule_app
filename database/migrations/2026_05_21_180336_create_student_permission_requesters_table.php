<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('student_permission_requesters')) {
            Schema::create('student_permission_requesters', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('user_id');
                $table->string('user_type'); // 'teacher' or 'staff'
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
                $table->index('schoolID');
                $table->index('user_id');
                $table->index('user_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_permission_requesters');
    }
};
