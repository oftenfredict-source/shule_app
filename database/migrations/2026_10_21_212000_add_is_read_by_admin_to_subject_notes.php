<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subject_notes') || Schema::hasColumn('subject_notes', 'is_read_by_admin')) {
            return;
        }

        Schema::table('subject_notes', function (Blueprint $table) {
            $table->boolean('is_read_by_admin')->default(false)->after('file_size');
        });

        DB::table('subject_notes')->update(['is_read_by_admin' => true]);
    }

    public function down(): void
    {
        if (Schema::hasTable('subject_notes') && Schema::hasColumn('subject_notes', 'is_read_by_admin')) {
            Schema::table('subject_notes', function (Blueprint $table) {
                $table->dropColumn('is_read_by_admin');
            });
        }
    }
};
