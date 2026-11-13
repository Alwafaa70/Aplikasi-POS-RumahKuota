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
            if (Schema::hasColumn('master_aplikasi_layanan', 'is_qris_tujuan')) {
                $table->dropColumn('is_qris_tujuan');
            }
        });

        Schema::table('pusat_aplikasi_layanan', function (Blueprint $table) {
            if (!Schema::hasColumn('pusat_aplikasi_layanan', 'is_qris_tujuan')) {
                $table->boolean('is_qris_tujuan')->default(false)->after('nomor_rekening');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pusat', function (Blueprint $table) {
            //
        });
    }
};
