<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('online_application_users')) {
            return;
        }

        Schema::table('online_application_users', function (Blueprint $table) {
            if (!Schema::hasColumn('online_application_users', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('online_application_users')) {
            return;
        }

        Schema::table('online_application_users', function (Blueprint $table) {
            if (Schema::hasColumn('online_application_users', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }
};
