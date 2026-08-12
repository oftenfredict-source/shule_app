<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'enable_social_media')) {
                $table->boolean('enable_social_media')->default(false)->after('organization_logo');
            }
            if (!Schema::hasColumn('schools', 'social_media')) {
                $table->json('social_media')->nullable()->after('enable_social_media');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('schools')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'social_media')) {
                $table->dropColumn('social_media');
            }
            if (Schema::hasColumn('schools', 'enable_social_media')) {
                $table->dropColumn('enable_social_media');
            }
        });
    }
};
