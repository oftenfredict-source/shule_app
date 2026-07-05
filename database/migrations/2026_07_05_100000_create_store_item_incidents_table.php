<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_item_incidents', function (Blueprint $table) {
            $table->bigIncrements('incidentID');
            $table->unsignedBigInteger('schoolID');
            $table->enum('incident_type', ['damaged', 'lost']);
            $table->unsignedBigInteger('stock_unitID');
            $table->unsignedBigInteger('itemID');
            $table->string('label');
            $table->enum('store_method', ['package', 'unit']);
            $table->unsignedBigInteger('packageID')->nullable();
            $table->unsignedBigInteger('unitID')->nullable();
            $table->string('package_unit_display')->nullable();
            $table->string('take_level')->nullable();
            $table->unsignedBigInteger('take_packageID')->nullable();
            $table->string('take_display')->nullable();
            $table->decimal('quantity_affected', 15, 4)->default(1);
            $table->enum('responsible_type', ['teacher', 'staff', 'student', 'other']);
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->string('responsible_name');
            $table->text('reason');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->decimal('payment_amount', 15, 2)->nullable();
            $table->enum('resolve_status', ['open', 'resolved'])->default('open');
            $table->enum('resolve_method', ['cash', 'replace_new'])->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by')->nullable();
            $table->string('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_item_incidents');
    }
};
