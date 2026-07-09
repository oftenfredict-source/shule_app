<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('black_book_punishment_categories', function (Blueprint $table) {
            $table->enum('punishment_type', ['one_time', 'continuous'])->default('one_time')->after('name');
            $table->boolean('requires_monitoring')->default(false)->after('punishment_type');
        });

        DB::statement("ALTER TABLE black_book_reports MODIFY COLUMN status ENUM('pending', 'viewed', 'resolved', 'monitored') NOT NULL DEFAULT 'pending'");

        Schema::table('black_book_reports', function (Blueprint $table) {
            $table->enum('punishment_type', ['one_time', 'continuous'])->nullable()->after('punishment_description');
            $table->boolean('requires_monitoring')->default(false)->after('punishment_type');
            $table->date('monitoring_deadline')->nullable()->after('requires_monitoring');
            $table->timestamp('monitoring_ended_at')->nullable()->after('monitoring_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('black_book_reports', function (Blueprint $table) {
            $table->dropColumn(['punishment_type', 'requires_monitoring', 'monitoring_deadline', 'monitoring_ended_at']);
        });

        DB::statement("ALTER TABLE black_book_reports MODIFY COLUMN status ENUM('pending', 'viewed', 'resolved') NOT NULL DEFAULT 'pending'");

        Schema::table('black_book_punishment_categories', function (Blueprint $table) {
            $table->dropColumn(['punishment_type', 'requires_monitoring']);
        });
    }
};
