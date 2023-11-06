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
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->unsignedBigInteger('ticket_category_id')->after('id');
            $table->foreign('ticket_category_id')->references('id')->on('ticket_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign('tickets_ticket_category_id_foreign');
            $table->dropColumn('ticket_category_id');
            $table->string('category', 255);
        });
    }
};
