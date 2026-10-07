<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_minutes')) {
            Schema::create('school_board_minutes', function (Blueprint $table) {
                $table->bigIncrements('minuteID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('meetingID');
                $table->string('title');
                $table->longText('content')->nullable();
                $table->text('decisions_summary')->nullable();
                $table->unsignedBigInteger('prepared_by')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->string('status', 30)->default('Draft'); // Draft, Submitted, Approved
                $table->timestamp('approved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['schoolID', 'meetingID']);
                $table->index(['schoolID', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_minutes');
    }
};
