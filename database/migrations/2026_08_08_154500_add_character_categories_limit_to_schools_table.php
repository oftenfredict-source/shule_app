<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'character_categories_limit')) {
                $table->unsignedTinyInteger('character_categories_limit')->default(5)->after('school_signature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'character_categories_limit')) {
                $table->dropColumn('character_categories_limit');
            }
        });
    }
};
