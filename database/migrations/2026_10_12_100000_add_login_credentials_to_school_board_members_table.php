<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_members')) {
            return;
        }

        Schema::table('school_board_members', function (Blueprint $table) {
            if (!Schema::hasColumn('school_board_members', 'username')) {
                $table->string('username', 100)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('school_board_members', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('username');
            }
        });

        Schema::table('school_board_members', function (Blueprint $table) {
            if (Schema::hasColumn('school_board_members', 'username')) {
                $table->unique(['schoolID', 'username'], 'school_board_members_school_username_unique');
            }
            if (Schema::hasColumn('school_board_members', 'user_id')) {
                $table->index('user_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('school_board_members')) {
            return;
        }

        Schema::table('school_board_members', function (Blueprint $table) {
            try {
                $table->dropUnique('school_board_members_school_username_unique');
            } catch (\Throwable $e) {
                // ignore
            }
            if (Schema::hasColumn('school_board_members', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('school_board_members', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};
