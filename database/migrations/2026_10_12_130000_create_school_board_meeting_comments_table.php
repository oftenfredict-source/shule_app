<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_meeting_comments')) {
            Schema::create('school_board_meeting_comments', function (Blueprint $table) {
                $table->bigIncrements('commentID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('meetingID');
                $table->unsignedBigInteger('memberID');
                $table->text('body');
                $table->boolean('was_absent')->default(false);
                $table->timestamps();

                $table->index(['schoolID', 'meetingID']);
                $table->index(['meetingID', 'created_at']);
                $table->index('memberID');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_meeting_comments');
    }
};
