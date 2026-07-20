<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_application_users', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('password');
            $table->rememberToken();
            $table->string('status', 20)->default('Active');
            $table->timestamps();

            $table->index('status');
        });

        if (Schema::hasTable('online_admissions')) {
            Schema::table('online_admissions', function (Blueprint $table) {
                if (!Schema::hasColumn('online_admissions', 'online_application_userID')) {
                    $table->unsignedBigInteger('online_application_userID')->nullable()->after('id');
                    $table->index('online_application_userID');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('online_admissions') && Schema::hasColumn('online_admissions', 'online_application_userID')) {
            Schema::table('online_admissions', function (Blueprint $table) {
                $table->dropIndex(['online_application_userID']);
                $table->dropColumn('online_application_userID');
            });
        }

        Schema::dropIfExists('online_application_users');
    }
};
