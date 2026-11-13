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
            $table->string('bukti_uang_fisik')->nullable();
            $table->enum('status_konfirmasi', ['menunggu_konfirmasi', 'dikonfirmasi'])->default('menunggu_konfirmasi');
            $table->text('catatan_admin')->nullable();
        });

        Schema::table('detail_closing_apk', function (Blueprint $table) {
            $table->string('bukti_saldo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_closing', function (Blueprint $table) {
            $table->dropColumn(['bukti_uang_fisik', 'status_konfirmasi', 'catatan_admin']);
        });

        Schema::table('detail_closing_apk', function (Blueprint $table) {
            $table->dropColumn('bukti_saldo');
        });
    }
};
