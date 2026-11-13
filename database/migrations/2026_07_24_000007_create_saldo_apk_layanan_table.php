<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel saldo_apk_layanan menyimpan saldo masing-masing Apk Layanan
     * per cabang. Admin Pusat juga punya saldonya sendiri (cabang_id = null
     * atau cabang_id milik pusat).
     *
     * Contoh:
     *   - Cabang A: Dana = Rp 500.000, OVO = Rp 300.000
     *   - Cabang B: Dana = Rp 200.000, BCA = Rp 1.000.000
     *   - Pusat:    Dana = Rp 2.000.000, BCA = Rp 5.000.000
     */
    public function up(): void
    {
        Schema::create('saldo_apk_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->foreignId('apk_layanan_id')->constrained('apk_layanan')->onDelete('cascade');
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();

            // Satu cabang hanya punya 1 record per apk layanan
            $table->unique(['cabang_id', 'apk_layanan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_apk_layanan');
    }
};
