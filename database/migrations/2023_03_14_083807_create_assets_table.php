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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('owner_id', 100)->nullable();
            $table->string('asset_type', 50);
            $table->string('brand', 50);
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('cpu', 50)->nullable();
            $table->string('ram', 20)->nullable();
            $table->string('utilization', 100);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->date('deployed_at')->nullable();
            $table->string('status', 50)->nullable();
            $table->unsignedBigInteger('purchase_id');
            $table->date('scrapped_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('no action');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('no action');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
