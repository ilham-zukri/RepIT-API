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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('requester_id', 100);
            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('handler_id', 100)->nullable();
            $table->foreign('handler_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('status', 50)->default('created');
            $table->text('description');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
