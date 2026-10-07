<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schools') && !Schema::hasColumn('schools', 'student_portal_enabled')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->boolean('student_portal_enabled')->default(false)->after('sms_enabled');
            });
        }

        if (!Schema::hasTable('student_portal_notes')) {
            Schema::create('student_portal_notes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('teacherID')->nullable();
                $table->unsignedBigInteger('classID')->nullable();
                $table->unsignedBigInteger('subclassID')->nullable();
                $table->unsignedBigInteger('subjectID')->nullable();
                $table->string('title');
                $table->text('body')->nullable();
                $table->string('file_path')->nullable();
                $table->string('original_name')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamps();
                $table->index(['schoolID', 'classID', 'is_published']);
            });
        }

        if (!Schema::hasTable('student_portal_books')) {
            Schema::create('student_portal_books', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('teacherID')->nullable();
                $table->unsignedBigInteger('classID')->nullable();
                $table->unsignedBigInteger('subclassID')->nullable();
                $table->unsignedBigInteger('subjectID')->nullable();
                $table->string('title');
                $table->string('author')->nullable();
                $table->text('description')->nullable();
                $table->string('file_path')->nullable();
                $table->string('original_name')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamps();
                $table->index(['schoolID', 'classID', 'is_published']);
            });
        }

        if (!Schema::hasTable('student_portal_quizzes')) {
            Schema::create('student_portal_quizzes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('teacherID')->nullable();
                $table->unsignedBigInteger('classID')->nullable();
                $table->unsignedBigInteger('subclassID')->nullable();
                $table->unsignedBigInteger('subjectID')->nullable();
                $table->string('title');
                $table->text('instructions')->nullable();
                $table->boolean('is_published')->default(true);
                $table->timestamps();
                $table->index(['schoolID', 'classID', 'is_published']);
            });
        }

        if (!Schema::hasTable('student_portal_quiz_questions')) {
            Schema::create('student_portal_quiz_questions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quiz_id');
                $table->text('question_text');
                $table->string('option_a');
                $table->string('option_b');
                $table->string('option_c')->nullable();
                $table->string('option_d')->nullable();
                $table->char('correct_option', 1);
                $table->unsignedSmallInteger('sort_order')->default(1);
                $table->timestamps();
                $table->index('quiz_id');
            });
        }

        if (!Schema::hasTable('student_portal_quiz_attempts')) {
            Schema::create('student_portal_quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quiz_id');
                $table->unsignedBigInteger('studentID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedSmallInteger('score')->default(0);
                $table->unsignedSmallInteger('total')->default(0);
                $table->json('answers')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
                $table->unique(['quiz_id', 'studentID']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_portal_quiz_attempts');
        Schema::dropIfExists('student_portal_quiz_questions');
        Schema::dropIfExists('student_portal_quizzes');
        Schema::dropIfExists('student_portal_books');
        Schema::dropIfExists('student_portal_notes');

        if (Schema::hasColumn('schools', 'student_portal_enabled')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn('student_portal_enabled');
            });
        }
    }
};
