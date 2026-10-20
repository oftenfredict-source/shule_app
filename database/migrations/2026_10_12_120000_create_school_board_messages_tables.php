<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_messages')) {
            Schema::create('school_board_messages', function (Blueprint $table) {
                $table->bigIncrements('messageID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('sender_memberID');
                $table->unsignedBigInteger('parent_messageID')->nullable()->index();
                $table->string('subject', 200);
                $table->text('body');
                $table->string('audience', 20)->default('selected'); // all | selected
                $table->unsignedInteger('recipient_count')->default(0);
                $table->timestamps();

                $table->index(['schoolID', 'created_at']);
                $table->index('sender_memberID');
            });
        }

        if (!Schema::hasTable('school_board_message_recipients')) {
            Schema::create('school_board_message_recipients', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('messageID');
                $table->unsignedBigInteger('memberID');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->unique(['messageID', 'memberID'], 'sb_msg_recipient_unique');
                $table->index(['schoolID', 'memberID', 'read_at']);
                $table->index('messageID');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_message_recipients');
        Schema::dropIfExists('school_board_messages');
    }
};
