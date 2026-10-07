<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('school_board_resolutions')) {
            Schema::create('school_board_resolutions', function (Blueprint $table) {
                $table->bigIncrements('resolutionID');
                $table->unsignedBigInteger('schoolID');
                $table->unsignedBigInteger('meetingID')->nullable();
                $table->string('resolution_number', 50)->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('proposed_by')->nullable();
                $table->unsignedBigInteger('seconded_by')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->date('resolution_date')->nullable();
                $table->date('implementation_deadline')->nullable();
                $table->string('status', 30)->default('Proposed'); // Proposed, Adopted, Rejected, Deferred, Implemented
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['schoolID', 'status']);
                $table->index(['schoolID', 'meetingID']);
                $table->unique(['schoolID', 'resolution_number'], 'sbr_school_resolution_number_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('school_board_resolutions');
    }
};
