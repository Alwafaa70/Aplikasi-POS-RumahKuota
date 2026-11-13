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
        Schema::table('setoran_closings', function (Blueprint $table) {
            $table->unsignedBigInteger('laporan_closing_id')->nullable()->change();
            $table->unsignedBigInteger('cabang_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setoran_closings', function (Blueprint $table) {
            $table->unsignedBigInteger('laporan_closing_id')->nullable(false)->change();
            $table->unsignedBigInteger('cabang_id')->nullable(false)->change();
        });
    }
};
