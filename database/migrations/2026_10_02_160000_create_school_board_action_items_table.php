<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_action_items')) {
            Schema::create('school_board_action_items', function (Blueprint $table) {
                $table->bigIncrements('actionItemID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('meetingID')->nullable();
                $table->unsignedBigInteger('resolutionID')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->date('due_date')->nullable();
                $table->string('priority', 20)->default('Medium'); // Low, Medium, High
                $table->string('status', 30)->default('Open'); // Open, In Progress, Completed, Cancelled
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'status']);
                $table->index(['schoolID', 'meetingID']);
                $table->index(['schoolID', 'assigned_to']);
                $table->index(['schoolID', 'due_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_action_items');
    }
};
