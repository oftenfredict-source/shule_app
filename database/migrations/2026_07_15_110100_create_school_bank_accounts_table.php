<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_bank_accounts', function (Blueprint $table) {
            $table->id('accountID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('bankID');
            $table->string('account_number', 100);
            $table->string('account_name', 200)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('schoolID')->references('schoolID')->on('schools')->onDelete('cascade');
            $table->foreign('bankID')->references('bankID')->on('banks')->onDelete('restrict');
            $table->unique(['schoolID', 'bankID', 'account_number'], 'school_bank_account_unique');
            $table->index(['schoolID', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_bank_accounts');
    }
};
