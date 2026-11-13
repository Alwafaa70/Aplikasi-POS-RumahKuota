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
            $table->unsignedBigInteger('pusat_aplikasi_layanan_id')->nullable()->after('cabang_id');
            $table->foreign('pusat_aplikasi_layanan_id')->references('id')->on('pusat_aplikasi_layanan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setoran_closings', function (Blueprint $table) {
            $table->dropForeign(['pusat_aplikasi_layanan_id']);
            $table->dropColumn('pusat_aplikasi_layanan_id');
        });
    }
};
