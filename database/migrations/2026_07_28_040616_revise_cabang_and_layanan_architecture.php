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
        // 1. Update tabel cabang
        Schema::table('cabang', function (Blueprint $table) {
            $table->string('area')->nullable()->after('nama_cabang');
            $table->decimal('saldo_awal_cash', 15, 2)->default(0)->after('alamat');
            $table->decimal('saldo_cash_sekarang', 15, 2)->default(0)->after('saldo_awal_cash');
        });

        // 2. Buat tabel master_aplikasi_layanan
        Schema::create('master_aplikasi_layanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_layanan'); // misal: Dana, BCA, Mandiri, Digipos
            $table->string('jenis_layanan'); // misal: Bank, E-Wallet, Pulsa
            $table->timestamps();
        });

        // 3. Buat tabel cabang_aplikasi_layanan (Pivot / Child dompet Cabang)
        Schema::create('cabang_aplikasi_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->foreignId('master_aplikasi_layanan_id')->constrained('master_aplikasi_layanan')->onDelete('cascade');
            $table->string('nomor_rekening');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('saldo_sekarang', 15, 2)->default(0);
            $table->boolean('is_qris_tujuan')->default(false);
            $table->timestamps();
        });

        // 4. Buat tabel pusat_aplikasi_layanan (Pivot / Child dompet Pusat)
        Schema::create('pusat_aplikasi_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_aplikasi_layanan_id')->constrained('master_aplikasi_layanan')->onDelete('cascade');
            $table->string('nomor_rekening');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('saldo_sekarang', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pusat_aplikasi_layanan');
        Schema::dropIfExists('cabang_aplikasi_layanan');
        Schema::dropIfExists('master_aplikasi_layanan');
        
        Schema::table('cabang', function (Blueprint $table) {
            $table->dropColumn(['area', 'saldo_awal_cash', 'saldo_cash_sekarang']);
        });
    }
};
