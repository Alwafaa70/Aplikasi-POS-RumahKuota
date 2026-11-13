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
        Schema::table('master_aplikasi_layanan', function (Blueprint $table) {
            $table->boolean('is_qris_tujuan')->default(false)->after('jenis_layanan');
        });

        Schema::table('cabang_aplikasi_layanan', function (Blueprint $table) {
            $table->dropColumn('is_qris_tujuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cabang_aplikasi_layanan', function (Blueprint $table) {
            $table->boolean('is_qris_tujuan')->default(false)->after('saldo_sekarang');
        });

        Schema::table('master_aplikasi_layanan', function (Blueprint $table) {
            $table->dropColumn('is_qris_tujuan');
        });
    }
};
