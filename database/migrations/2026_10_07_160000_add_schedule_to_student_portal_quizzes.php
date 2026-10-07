<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_portal_quizzes') && !Schema::hasColumn('student_portal_quizzes', 'opens_at')) {
            Schema::table('student_portal_quizzes', function (Blueprint $table) {
                $table->timestamp('opens_at')->nullable()->after('is_published');
                $table->timestamp('closes_at')->nullable()->after('opens_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_portal_quizzes', 'opens_at')) {
            Schema::table('student_portal_quizzes', function (Blueprint $table) {
                $table->dropColumn(['opens_at', 'closes_at']);
            });
        }
    }
};
