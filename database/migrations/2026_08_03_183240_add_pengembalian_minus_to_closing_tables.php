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
        Schema::table('laporan_closing', function (Blueprint $table) {
            $table->enum('status_pengembalian_minus', ['belum', 'dikembalikan'])->default('belum');
            $table->text('catatan_pengembalian')->nullable();
        });

        Schema::table('detail_closing_apk', function (Blueprint $table) {
            $table->enum('status_pengembalian_minus', ['belum', 'dikembalikan'])->default('belum');
            $table->text('catatan_pengembalian')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_closing', function (Blueprint $table) {
            $table->dropColumn(['status_pengembalian_minus', 'catatan_pengembalian']);
        });

        Schema::table('detail_closing_apk', function (Blueprint $table) {
            $table->dropColumn(['status_pengembalian_minus', 'catatan_pengembalian']);
        });
    }
};
