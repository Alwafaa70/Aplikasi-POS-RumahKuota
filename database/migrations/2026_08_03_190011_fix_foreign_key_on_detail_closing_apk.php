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
        Schema::table('detail_closing_apk', function (Blueprint $table) {
            $table->dropForeign(['apk_layanan_id']);
            $table->foreign('apk_layanan_id')->references('id')->on('master_aplikasi_layanan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_closing_apk', function (Blueprint $table) {
            $table->dropForeign(['apk_layanan_id']);
            $table->foreign('apk_layanan_id')->references('id')->on('apk_layanan')->onDelete('cascade');
        });
    }
};
