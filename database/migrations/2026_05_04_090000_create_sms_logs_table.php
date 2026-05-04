<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('schoolID');
            $table->string('message_id')->nullable()->comment('ID returned from SMS API');
            $table->string('message_type')->comment('parent, class_parent, teacher, staff, all_users, fee, result, etc.');
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('recipient_role')->comment('parent, teacher, staff');
            $table->string('sent_by_name')->nullable()->comment('Name of user who sent the SMS');
            $table->string('sent_by_role')->nullable()->comment('admin, teacher, etc.');
            $table->text('message')->nullable();
            $table->string('status')->default('PENDING')->comment('PENDING, DELIVERED, FAILED');
            $table->date('sent_date');
            $table->time('sent_time');
            $table->timestamps();

            $table->index(['schoolID', 'message_type']);
            $table->index(['schoolID', 'sent_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
