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
        Schema::create('request_operasional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->enum('jenis_request', ['barang_fisik', 'cash_operasional', 'saldo_digital']);
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_request_operasional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('request_operasional')->onDelete('cascade');
            $table->foreignId('barang_fisik_id')->nullable()->constrained('master_barang')->onDelete('cascade');
            $table->foreignId('apk_layanan_id')->nullable()->constrained('apk_layanan')->onDelete('cascade');
            $table->decimal('nominal_atau_qty', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_request_operasional');
        Schema::dropIfExists('request_operasional');
    }
};
