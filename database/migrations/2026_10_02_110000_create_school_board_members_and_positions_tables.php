<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_positions')) {
            Schema::create('school_board_positions', function (Blueprint $table) {
                $table->bigIncrements('positionID');
                $table->unsignedBigInteger('schoolID');
                $table->string('position_name');
                $table->string('position_code', 50)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(100);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['schoolID', 'is_active']);
                $table->unique(['schoolID', 'position_name']);
            });
        }

        if (!Schema::hasTable('school_board_members')) {
            Schema::create('school_board_members', function (Blueprint $table) {
                $table->bigIncrements('memberID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('positionID')->nullable();
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('last_name');
                $table->string('gender', 20)->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('occupation')->nullable();
                $table->string('organization')->nullable();
                $table->string('national_id')->nullable();
                $table->text('address')->nullable();
                $table->date('appointed_date')->nullable();
                $table->date('term_end_date')->nullable();
                $table->string('status', 20)->default('Active'); // Active, Inactive, Former
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'status']);
                $table->index(['schoolID', 'positionID']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_members');
        Schema::dropIfExists('school_board_positions');
    }
};
