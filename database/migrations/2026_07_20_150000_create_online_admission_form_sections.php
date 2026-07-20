<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_form_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schoolID');
            $table->string('section_key', 80);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['schoolID', 'section_key'], 'oa_form_sections_school_key_unique');
            $table->index(['schoolID', 'is_active', 'sort_order'], 'oa_form_sections_school_active_order');
        });

        Schema::table('online_admission_form_fields', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->after('schoolID');
            $table->index(['schoolID', 'section_id'], 'oa_form_fields_school_section');
        });
    }

    public function down(): void
    {
        Schema::table('online_admission_form_fields', function (Blueprint $table) {
            $table->dropIndex('oa_form_fields_school_section');
            $table->dropColumn('section_id');
        });

        Schema::dropIfExists('online_admission_form_sections');
    }
};
