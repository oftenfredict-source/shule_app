<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subject_notes') || Schema::hasColumn('subject_notes', 'kind')) {
            return;
        }

        Schema::table('subject_notes', function (Blueprint $table) {
            $table->string('kind', 20)->default('note')->after('title');
            $table->index(['schoolID', 'kind']);
        });

        DB::table('subject_notes')->whereNull('kind')->orWhere('kind', '')->update(['kind' => 'note']);
    }

    public function down(): void
    {
        if (Schema::hasTable('subject_notes') && Schema::hasColumn('subject_notes', 'kind')) {
            Schema::table('subject_notes', function (Blueprint $table) {
                $table->dropIndex(['schoolID', 'kind']);
                $table->dropColumn('kind');
            });
        }
    }
};
