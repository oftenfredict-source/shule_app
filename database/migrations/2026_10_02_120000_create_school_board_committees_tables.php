<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_committees')) {
            Schema::create('school_board_committees', function (Blueprint $table) {
                $table->bigIncrements('committeeID');
                $table->unsignedBigInteger('schoolID');
                $table->string('committee_name');
                $table->string('committee_code', 50)->nullable();
                $table->text('description')->nullable();
                $table->string('status', 20)->default('Active'); // Active, Inactive
                $table->date('formed_date')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'status']);
                $table->unique(['schoolID', 'committee_name']);
            });
        }

        if (!Schema::hasTable('school_board_committee_members')) {
            Schema::create('school_board_committee_members', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('committeeID');
                $table->unsignedBigInteger('memberID');
                $table->string('committee_role', 50)->default('Member'); // Chair, Secretary, Member
                $table->date('assigned_date')->nullable();
                $table->timestamps();

                $table->unique(['committeeID', 'memberID'], 'sbcm_committee_member_unique');
                $table->index(['schoolID', 'committeeID']);
                $table->index(['schoolID', 'memberID']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_committee_members');
        Schema::dropIfExists('school_board_committees');
    }
};
