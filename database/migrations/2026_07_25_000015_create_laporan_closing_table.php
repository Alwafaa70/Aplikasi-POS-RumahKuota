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
        Schema::create('laporan_closing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->date('tanggal');
            
            // Uang Cash Fisik
            $table->decimal('uang_fisik_aktual', 15, 2);
            $table->decimal('uang_fisik_sistem', 15, 2);
            $table->decimal('selisih_fisik', 15, 2);
            $table->enum('status_fisik', ['balance', 'plus', 'minus']);
            
            $table->text('catatan_kasir')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_closing_apk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('closing_id')->constrained('laporan_closing')->onDelete('cascade');
            $table->foreignId('apk_layanan_id')->constrained('apk_layanan')->onDelete('cascade');
            
            $table->decimal('saldo_aktual', 15, 2);
            $table->decimal('saldo_sistem', 15, 2);
            $table->decimal('selisih', 15, 2);
            $table->enum('status', ['balance', 'plus', 'minus']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_closing_apk');
        Schema::dropIfExists('laporan_closing');
    }
};
