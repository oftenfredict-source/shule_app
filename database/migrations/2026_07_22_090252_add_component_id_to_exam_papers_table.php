<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->unsignedBigInteger('componentID')->nullable()->after('class_subjectID');
            $table->foreign('componentID', 'exam_papers_component_fk')
                ->references('componentID')
                ->on('school_subject_components')
                ->onDelete('set null');
            $table->index(['examID', 'class_subjectID', 'componentID'], 'exam_papers_exam_cs_comp_idx');
        });
    }

    public function down(): void
    {
        Schema::table('exam_papers', function (Blueprint $table) {
            $table->dropForeign('exam_papers_component_fk');
            $table->dropIndex('exam_papers_exam_cs_comp_idx');
            $table->dropColumn('componentID');
        });
    }
};
