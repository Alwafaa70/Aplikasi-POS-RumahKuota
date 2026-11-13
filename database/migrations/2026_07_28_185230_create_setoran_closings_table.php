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
        Schema::create('setoran_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_closing_id')->constrained('laporan_closing')->onDelete('cascade');
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->decimal('nominal', 15, 2);
            $table->enum('status', ['menunggu_konfirmasi', 'diterima', 'ditolak'])->default('menunggu_konfirmasi');
            $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('waktu_dikonfirmasi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran_closings');
    }
};
