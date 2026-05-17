<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create permission_approval_chains table
        if (!Schema::hasTable('permission_approval_chains')) {
            Schema::create('permission_approval_chains', function (Blueprint $table) {
                $table->id('chainID');
                $table->unsignedBigInteger('schoolID');
                $table->string('permission_type'); // student, teacher, staff
                $table->integer('approval_order');
                $table->unsignedBigInteger('role_id')->nullable(); // Spatie role ID
                $table->unsignedBigInteger('profession_id')->nullable(); // Staff position ID
                $table->string('role_name')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('schoolID');
                $table->index('permission_type');
            });
        }

        // 2. Create permission_approval_logs table
        if (!Schema::hasTable('permission_approval_logs')) {
            Schema::create('permission_approval_logs', function (Blueprint $table) {
                $table->id('logID');
                $table->unsignedBigInteger('permissionID');
                $table->unsignedBigInteger('approver_id')->nullable();
                $table->string('approver_role')->nullable();
                $table->integer('approval_order');
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->text('admin_response')->nullable();
                $table->timestamps();

                // Indexes
                $table->index('permissionID');
            });
        }

        // 3. Add columns to permission_requests
        if (Schema::hasTable('permission_requests')) {
            Schema::table('permission_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('permission_requests', 'current_approval_order')) {
                    $table->integer('current_approval_order')->default(1)->after('status');
                }
                if (!Schema::hasColumn('permission_requests', 'is_chain_completed')) {
                    $table->boolean('is_chain_completed')->default(false)->after('current_approval_order');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_approval_chains');
        Schema::dropIfExists('permission_approval_logs');

        if (Schema::hasTable('permission_requests')) {
            Schema::table('permission_requests', function (Blueprint $table) {
                if (Schema::hasColumn('permission_requests', 'current_approval_order')) {
                    $table->dropColumn('current_approval_order');
                }
                if (Schema::hasColumn('permission_requests', 'is_chain_completed')) {
                    $table->dropColumn('is_chain_completed');
                }
            });
        }
    }
};
