<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_requisitions', function (Blueprint $table) {
            $table->date('expected_return_date')->nullable()->after('requisition_status');
            $table->text('return_description')->nullable()->after('returned_at');
            $table->enum('return_condition', ['good', 'bad'])->nullable()->after('return_description');
            $table->text('bad_description')->nullable()->after('return_condition');
        });

        DB::statement("ALTER TABLE store_requisitions MODIFY recipient_type ENUM('teacher', 'staff', 'student') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('store_requisitions', function (Blueprint $table) {
            $table->dropColumn([
                'expected_return_date',
                'return_description',
                'return_condition',
                'bad_description',
            ]);
        });

        DB::statement("ALTER TABLE store_requisitions MODIFY recipient_type ENUM('teacher', 'staff') NOT NULL");
    }
};
