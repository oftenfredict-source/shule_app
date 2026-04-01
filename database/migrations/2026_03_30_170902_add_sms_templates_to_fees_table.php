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
        Schema::table('fees', function (Blueprint $row) {
            $row->text('sms_assignment_template')->nullable()->after('description');
            $row->text('sms_reminder_template')->nullable()->after('sms_assignment_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees', function (Blueprint $row) {
            $row->dropColumn(['sms_assignment_template', 'sms_reminder_template']);
        });
    }
};
