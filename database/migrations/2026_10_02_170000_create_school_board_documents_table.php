<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_documents')) {
            Schema::create('school_board_documents', function (Blueprint $table) {
                $table->bigIncrements('documentID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('meetingID')->nullable();
                $table->unsignedBigInteger('resolutionID')->nullable();
                $table->unsignedBigInteger('committeeID')->nullable();
                $table->string('title');
                $table->string('category', 50)->default('General'); // General, Agenda, Minutes, Resolution, Policy, Report, Other
                $table->text('description')->nullable();
                $table->string('file_path');
                $table->string('original_filename')->nullable();
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('status', 20)->default('Active'); // Active, Archived
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->string('uploaded_by_name')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'status']);
                $table->index(['schoolID', 'category']);
                $table->index(['schoolID', 'meetingID']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_documents');
    }
};
