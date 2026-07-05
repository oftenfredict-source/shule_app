<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_inventory_actors', function (Blueprint $table) {
            $table->bigIncrements('actorID');
            $table->unsignedBigInteger('schoolID');
            $table->enum('actor_type', ['teacher', 'staff']);
            $table->unsignedBigInteger('actor_ref_id');
            $table->string('actor_name');
            $table->enum('category_scope', ['all', 'specific'])->default('all');
            $table->json('category_ids')->nullable();
            $table->json('privileges');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('granted_by')->nullable();
            $table->timestamps();

            $table->unique(['schoolID', 'actor_type', 'actor_ref_id'], 'store_actor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_inventory_actors');
    }
};
