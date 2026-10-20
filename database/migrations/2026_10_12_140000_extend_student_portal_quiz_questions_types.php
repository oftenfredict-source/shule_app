<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_portal_quiz_questions')) {
            return;
        }

        Schema::table('student_portal_quiz_questions', function (Blueprint $table) {
            if (!Schema::hasColumn('student_portal_quiz_questions', 'question_type')) {
                $table->string('question_type', 40)->default('multiple_choice')->after('quiz_id');
            }
            if (!Schema::hasColumn('student_portal_quiz_questions', 'section_number')) {
                $table->unsignedSmallInteger('section_number')->default(1)->after('question_type');
            }
            if (!Schema::hasColumn('student_portal_quiz_questions', 'section_title')) {
                $table->string('section_title', 200)->nullable()->after('section_number');
            }
            if (!Schema::hasColumn('student_portal_quiz_questions', 'item_label')) {
                $table->string('item_label', 20)->nullable()->after('section_title');
            }
            if (!Schema::hasColumn('student_portal_quiz_questions', 'points')) {
                $table->decimal('points', 8, 2)->default(1)->after('item_label');
            }
            if (!Schema::hasColumn('student_portal_quiz_questions', 'config')) {
                $table->json('config')->nullable()->after('correct_option');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('student_portal_quiz_questions')) {
            return;
        }

        Schema::table('student_portal_quiz_questions', function (Blueprint $table) {
            foreach (['question_type', 'section_number', 'section_title', 'item_label', 'points', 'config'] as $col) {
                if (Schema::hasColumn('student_portal_quiz_questions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
