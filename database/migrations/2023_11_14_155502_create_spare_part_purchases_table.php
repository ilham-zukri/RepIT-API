<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spare_part_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('request_id');
            $table->foreign('request_id')->references('id')->on('spare_part_requests')->onDelete('cascade');
           $table->string('purchased_by_id', 255);
            $table->foreign('purchased_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('purchased_from', 255);
            $table->bigInteger('total_price');
            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('purchase_statuses')->onDelete('cascade');
            $table->text('description');
            $table->string('doc_path', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_part_purchases');
    }
};
