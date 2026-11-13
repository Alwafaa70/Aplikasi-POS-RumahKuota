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
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->unsignedBigInteger('cabang_apk_layanan_id')->nullable()->after('jenis_item');
            $table->foreign('cabang_apk_layanan_id')->references('id')->on('cabang_aplikasi_layanan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropForeign(['cabang_apk_layanan_id']);
            $table->dropColumn('cabang_apk_layanan_id');
        });
    }
};
