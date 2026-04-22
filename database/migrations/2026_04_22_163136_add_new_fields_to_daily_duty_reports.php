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
        Schema::table('daily_duty_reports', function (Blueprint $table) {
            $table->string('meals')->nullable()->after('pupils_cleanliness');
            $table->string('english_speaking')->nullable()->after('special_events');
            $table->text('sick_students_list')->nullable()->after('english_speaking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_duty_reports', function (Blueprint $table) {
            $table->dropColumn(['meals', 'english_speaking', 'sick_students_list']);
        });
    }
};
