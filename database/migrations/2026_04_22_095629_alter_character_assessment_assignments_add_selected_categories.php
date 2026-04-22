<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds the missing 'selected_categories' JSON column to character_assessment_assignments.
     */
    public function up(): void
    {
        Schema::table('character_assessment_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('character_assessment_assignments', 'selected_categories')) {
                $table->json('selected_categories')->nullable()->after('teacherID');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_assessment_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('character_assessment_assignments', 'selected_categories')) {
                $table->dropColumn('selected_categories');
            }
        });
    }
};
