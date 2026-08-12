<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('home_packages')) {
            Schema::create('home_packages', function (Blueprint $table) {
                $table->id('home_packageID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('academic_yearID');
                $table->unsignedBigInteger('holidayID');
                $table->unsignedBigInteger('teacherID');
                $table->enum('level', ['O_Level', 'A_Level', 'Primary'])->default('O_Level');
                $table->string('title', 255)->nullable();
                $table->string('file_path', 500);
                $table->string('original_filename', 255);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->timestamps();

                $table->unique(['teacherID', 'holidayID'], 'home_packages_teacher_holiday_unique');
                $table->index(['schoolID', 'academic_yearID']);
                $table->index(['schoolID', 'level']);
                $table->index('holidayID');
            });
        }

        if (!Schema::hasTable('parent_home_package_notifications')) {
            Schema::create('parent_home_package_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('parentID');
                $table->unsignedBigInteger('home_packageID');
                $table->boolean('is_viewed')->default(false);
                $table->timestamps();

                $table->unique(['parentID', 'home_packageID'], 'phpn_parent_package_unique');
                $table->index(['parentID', 'is_viewed']);
                $table->index('schoolID');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_home_package_notifications');
        Schema::dropIfExists('home_packages');
    }
};
