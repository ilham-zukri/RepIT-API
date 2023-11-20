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
        Schema::table('priorities', function (Blueprint $table) {
            $table->integer('max_response_time')->nullable()->after('priority');
            $table->integer('max_resolve_time')->nullable()->after('max_response_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('priorities', function (Blueprint $table) {
            $table->dropColumn('max_response_time');
            $table->dropColumn('max_resolve_time');
        });
    }
};
