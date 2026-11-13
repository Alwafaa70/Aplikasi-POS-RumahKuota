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
        Schema::create('riwayat_aplikasi_layanan', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_apk', ['pusat', 'cabang']); // Apakah apk pusat atau cabang
            $table->unsignedBigInteger('apk_layanan_id'); // Merujuk ke pusat_aplikasi_layanan atau cabang_aplikasi_layanan
            $table->enum('jenis_mutasi', ['masuk', 'keluar']);
            $table->decimal('nominal', 15, 2);
            $table->decimal('saldo_akhir', 15, 2);
            $table->string('keterangan'); // Misalnya: "Penjualan QRIS Transaksi #12", "Mutasi ke Pusat", dll.
            $table->unsignedBigInteger('referensi_id')->nullable(); // ID dari transaksi, mutasi, dll.
            $table->string('referensi_type')->nullable(); // Model name misalnya 'App\Models\Transaksi'
            $table->timestamps();

            // Index
            $table->index(['jenis_apk', 'apk_layanan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_aplikasi_layanan');
    }
};
