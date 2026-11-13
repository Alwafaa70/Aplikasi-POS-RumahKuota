<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->unsignedBigInteger('pusat_apk_layanan_id')->nullable()->after('cabang_apk_layanan_id');
            $table->foreign('pusat_apk_layanan_id')->references('id')->on('pusat_aplikasi_layanan')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropForeign(['pusat_apk_layanan_id']);
            $table->dropColumn('pusat_apk_layanan_id');
        });
    }
};
