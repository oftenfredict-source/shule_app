<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Duo / multi-paper subjects (A-Level Secondary): e.g. Physics = PHY1 + PHY2 + PHY3.
     */
    public function up(): void
    {
        Schema::table('school_subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('school_subjects', 'is_duo')) {
                $table->boolean('is_duo')->default(false)->after('status');
            }
        });

        if (!Schema::hasTable('school_subject_components')) {
            Schema::create('school_subject_components', function (Blueprint $table) {
                $table->id('componentID');
                $table->unsignedBigInteger('subjectID');
                $table->string('component_name', 100);
                $table->unsignedTinyInteger('max_marks')->default(100); // 1–100
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('subjectID')
                    ->references('subjectID')
                    ->on('school_subjects')
                    ->onDelete('cascade');

                $table->index(['subjectID', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_subject_components');

        Schema::table('school_subjects', function (Blueprint $table) {
            if (Schema::hasColumn('school_subjects', 'is_duo')) {
                $table->dropColumn('is_duo');
            }
        });
    }
};
