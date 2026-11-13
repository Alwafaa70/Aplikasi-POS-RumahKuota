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
        Schema::table('pengaturan_ews', function (Blueprint $table) {
            $table->integer('safety_stock_percent')->default(20)->after('batas_ews');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_ews', function (Blueprint $table) {
            $table->dropColumn('safety_stock_percent');
        });
    }
};
