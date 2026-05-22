<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('student_permission_requesters')) {
            // Table exists, add individual indexes if they don't exist
            Schema::table('student_permission_requesters', function (Blueprint $table) {
                // Check and add individual indexes
                $indexes = collect(DB::select("SHOW INDEX FROM student_permission_requesters"))->pluck('Key_name')->unique();

                if (!$indexes->contains('student_permission_requesters_schoolid_index')) {
                    $table->index('schoolID');
                }
                if (!$indexes->contains('student_permission_requesters_user_id_index')) {
                    $table->index('user_id');
                }
                if (!$indexes->contains('student_permission_requesters_user_type_index')) {
                    $table->index('user_type');
                }
            });
        } else {
            // Table doesn't exist, create it with correct structure
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
        if (Schema::hasTable('student_permission_requesters')) {
            Schema::table('student_permission_requesters', function (Blueprint $table) {
                $table->dropIndex(['schoolID']);
                $table->dropIndex(['user_id']);
                $table->dropIndex(['user_type']);
            });
        }
    }
};
