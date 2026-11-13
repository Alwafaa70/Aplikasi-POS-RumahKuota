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
        Schema::create('transaksi_bantuan_pusat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->foreignId('produk_digital_b_id')->constrained('produk_digital_b')->onDelete('cascade');
            $table->string('nomor_tujuan');
            $table->decimal('amount', 15, 2); // Nominal transaksi pelanggan
            
            // Status: menunggu_pusat, selesai, batal
            $table->enum('status', ['menunggu_pusat', 'selesai', 'batal'])->default('menunggu_pusat');
            
            // Diisi oleh Admin Pusat saat memproses
            $table->decimal('harga_modal_pusat', 15, 2)->nullable();
            $table->foreignId('apk_layanan_pusat_id')->nullable()->constrained('apk_layanan')->onDelete('set null');
            
            // Diisi otomatis dari DigitalBService saat Kasir request
            $table->decimal('fee_cabang', 15, 2)->default(0); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_bantuan_pusat');
    }
};
