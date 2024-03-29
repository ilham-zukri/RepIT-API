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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchased_by', 150);
            $table->date('purchased_at')->nullable();
            $table->string('purchased_from', 100);
            $table->bigInteger('total_price')->default(0);
            $table->string('requested_by', 100);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('purchased_by')->references('id')->on('users')->onDelete('no action');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('no action');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
