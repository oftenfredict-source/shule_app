<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_move_batches', function (Blueprint $table) {
            $table->id('batchID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('to_academic_yearID');
            $table->unsignedBigInteger('moved_by')->nullable();
            $table->unsignedInteger('moved_count')->default(0);
            $table->unsignedInteger('graduated_count')->default(0);
            $table->enum('status', ['Completed', 'Undone'])->default('Completed');
            $table->timestamp('undone_at')->nullable();
            $table->unsignedBigInteger('undone_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('to_academic_yearID')->references('academic_yearID')->on('academic_years')->onDelete('cascade');
            $table->index(['schoolID', 'status'], 'smb_school_status_idx');
        });

        Schema::create('student_move_batch_items', function (Blueprint $table) {
            $table->id('itemID');
            $table->unsignedBigInteger('batchID');
            $table->unsignedBigInteger('studentID');
            $table->unsignedBigInteger('old_enrollmentID');
            $table->unsignedBigInteger('new_enrollmentID')->nullable();
            $table->enum('action', ['Promoted', 'Graduated']);
            $table->unsignedBigInteger('old_classID')->nullable();
            $table->unsignedBigInteger('old_subclassID')->nullable();
            $table->unsignedBigInteger('new_classID')->nullable();
            $table->unsignedBigInteger('new_subclassID')->nullable();
            $table->string('old_student_status', 50)->nullable();
            $table->string('new_student_status', 50)->nullable();
            $table->timestamps();

            $table->foreign('batchID')->references('batchID')->on('student_move_batches')->onDelete('cascade');
            $table->foreign('studentID')->references('studentID')->on('students')->onDelete('cascade');
            $table->foreign('old_enrollmentID')->references('enrollmentID')->on('student_enrollments')->onDelete('cascade');
            $table->foreign('new_enrollmentID')->references('enrollmentID')->on('student_enrollments')->onDelete('set null');
            $table->index(['batchID', 'studentID'], 'smbi_batch_student_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_move_batch_items');
        Schema::dropIfExists('student_move_batches');
    }
};
