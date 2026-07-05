<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_requisitions', function (Blueprint $table) {
            $table->bigIncrements('requisitionID');
            $table->unsignedBigInteger('schoolID');
            $table->unsignedBigInteger('stock_unitID');
            $table->unsignedBigInteger('itemID');
            $table->string('label');
            $table->enum('store_method', ['package', 'unit']);
            $table->unsignedBigInteger('packageID')->nullable();
            $table->unsignedBigInteger('unitID');
            $table->string('package_unit_display')->nullable();
            $table->enum('recipient_type', ['teacher', 'staff']);
            $table->unsignedBigInteger('recipient_id');
            $table->string('recipient_name');
            $table->enum('requisition_status', ['can_return', 'cant_return']);
            $table->string('status')->default('issued');
            $table->string('issued_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_requisitions');
    }
};
