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
        Schema::dropIfExists('peripherals');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('peripherals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('peripheral_types')->onDelete('cascade');
            $table->string('brand', 255);
            $table->string('model', 255);
            $table->string('serial_number', 255)->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->foreign('device_id')->references('id')->on('assets')->onDelete('cascade');
            $table->date('addded_at');
            $table->timestamps();
        });
    }
};
