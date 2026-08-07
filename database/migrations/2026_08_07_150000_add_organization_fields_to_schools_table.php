<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('is_under_organization')->default(false)->after('school_logo');
            $table->string('organization_name', 255)->nullable()->after('is_under_organization');
            $table->string('organization_logo', 255)->nullable()->after('organization_name');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'is_under_organization',
                'organization_name',
                'organization_logo',
            ]);
        });
    }
};
