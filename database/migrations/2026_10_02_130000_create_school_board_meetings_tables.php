<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_meetings')) {
            Schema::create('school_board_meetings', function (Blueprint $table) {
                $table->bigIncrements('meetingID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('committeeID')->nullable();
                $table->string('title');
                $table->string('meeting_type', 40)->default('Full Board'); // Full Board, Committee
                $table->date('meeting_date');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->string('venue')->nullable();
                $table->text('agenda')->nullable();
                $table->string('status', 30)->default('Scheduled'); // Scheduled, Completed, Cancelled, Postponed
                $table->unsignedBigInteger('chair_memberID')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'meeting_date']);
                $table->index(['schoolID', 'status']);
                $table->index(['schoolID', 'committeeID']);
            });
        }

        if (!Schema::hasTable('school_board_meeting_attendees')) {
            Schema::create('school_board_meeting_attendees', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('meetingID');
                $table->unsignedBigInteger('memberID');
                $table->string('attendance_status', 30)->default('Expected'); // Expected, Present, Absent, Excused
                $table->timestamps();

                $table->unique(['meetingID', 'memberID'], 'sbma_meeting_member_unique');
                $table->index(['schoolID', 'meetingID']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_meeting_attendees');
        Schema::dropIfExists('school_board_meetings');
    }
};
