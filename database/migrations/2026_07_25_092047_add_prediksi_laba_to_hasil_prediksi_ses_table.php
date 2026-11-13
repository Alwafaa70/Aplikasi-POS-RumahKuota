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
        Schema::table('hasil_prediksi_ses', function (Blueprint $table) {
            $table->decimal('prediksi_laba', 15, 2)->default(0)->after('angka_rekomendasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_prediksi_ses', function (Blueprint $table) {
            $table->dropColumn('prediksi_laba');
        });
    }
};
