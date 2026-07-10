<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parent_fingerprint_portal_classes')) {
            return;
        }

        Schema::create('parent_fingerprint_portal_classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('classID');
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->unique(['schoolID', 'classID'], 'parent_fp_portal_school_class_unique');
            $table->index(['schoolID', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_fingerprint_portal_classes');
    }
};
