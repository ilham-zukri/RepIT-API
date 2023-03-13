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
        Schema::create('purchases_details', function (Blueprint $table) {
            $table->id();
            $table->string('asset_type', 100)->nullable();
            $table->string('brand', 50)->nullable();
            $table->string('model', 100)->nullable();
            $table->integer('amount')->unsigned()->nullable();
            $table->bigInteger('price_ea')->nullable();
            $table->bigInteger('total_price')->nullable();
            $table->date('warranty_end')->nullable();
            $table->unsignedBigInteger('purchase_id');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases_details');
    }
};
