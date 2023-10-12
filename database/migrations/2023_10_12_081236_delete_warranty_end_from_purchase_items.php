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
        Schema::table('purchases_items', function (Blueprint $table) {
            $table->dropColumn('warranty_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases_items', function (Blueprint $table) {
            $table->date('warranty_end')->after('total_price')->nullable();
        });
    }
};
