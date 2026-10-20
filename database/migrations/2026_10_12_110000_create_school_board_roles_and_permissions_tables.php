<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_roles')) {
            Schema::create('school_board_roles', function (Blueprint $table) {
                $table->bigIncrements('boardRoleID');
                $table->unsignedBigInteger('schoolID');
                $table->string('role_name');
                $table->string('role_code', 50)->nullable();
                $table->string('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(100);
                $table->timestamps();

                $table->index(['schoolID', 'is_active']);
                $table->unique(['schoolID', 'role_name']);
            });
        }

        if (!Schema::hasTable('school_board_role_permissions')) {
            Schema::create('school_board_role_permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('boardRoleID');
                $table->string('name');
                $table->string('permission_category', 80)->nullable();
                $table->timestamps();

                $table->unique(['boardRoleID', 'name'], 'sb_role_perm_unique');
                $table->index('boardRoleID');
            });
        }

        if (!Schema::hasTable('school_board_member_roles')) {
            Schema::create('school_board_member_roles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('memberID');
                $table->unsignedBigInteger('boardRoleID');
                $table->timestamps();

                $table->unique(['memberID', 'boardRoleID'], 'sb_member_role_unique');
                $table->index(['schoolID', 'memberID']);
                $table->index('boardRoleID');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_member_roles');
        Schema::dropIfExists('school_board_role_permissions');
        Schema::dropIfExists('school_board_roles');
    }
};
