<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_item_requests', function (Blueprint $table) {
            $table->bigIncrements('requestID');
            $table->unsignedBigInteger('schoolID');
            $table->enum('requester_type', ['teacher', 'staff']);
            $table->unsignedBigInteger('requester_id');
            $table->string('requester_name');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->json('issues');
            $table->text('items_summary')->nullable();
            $table->text('notes')->nullable();
            $table->date('expected_return_date')->nullable();
            $table->string('requested_by')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_item_requests');
    }
};
