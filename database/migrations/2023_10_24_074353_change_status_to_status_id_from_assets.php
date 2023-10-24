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
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->unsignedBigInteger('status_id')->after('deployed_at')->default(1);
            $table->foreign('status_id')->references('id')->on('asset_statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign('assets_status_id_foreign');
            $table->dropColumn('status_ud');
            $table->string('status', 100)->nullable();

        });
    }
};
