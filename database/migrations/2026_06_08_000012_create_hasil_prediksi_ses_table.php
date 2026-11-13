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
        Schema::create('hasil_prediksi_ses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('master_barang')->onDelete('cascade');
            $table->string('periode_prediksi'); // Format: YYYY-MM
            $table->decimal('angka_rekomendasi', 10, 2);
            $table->timestamps();

            $table->unique(['cabang_id', 'barang_id', 'periode_prediksi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_prediksi_ses');
    }
};
