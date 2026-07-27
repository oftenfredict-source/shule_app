<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('web_visitors', 'email')) {
                $table->string('email', 160)->nullable()->after('ip_address')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('web_visitors', function (Blueprint $table) {
            if (Schema::hasColumn('web_visitors', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
