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
        Schema::create('mutasi_uang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cabang_asal_id')->nullable()->constrained('cabang')->onDelete('cascade'); // Jika null, berarti Pusat
            $table->foreignId('cabang_tujuan_id')->constrained('cabang')->onDelete('cascade');
            $table->enum('jenis_mutasi', ['cash', 'saldo_digital']);
            $table->foreignId('apk_sumber_id')->nullable()->constrained('apk_layanan')->onDelete('set null');
            $table->foreignId('apk_tujuan_id')->nullable()->constrained('apk_layanan')->onDelete('set null');
            $table->decimal('nominal', 15, 2);
            $table->decimal('fee_admin', 15, 2)->default(0); // Untuk biaya potong admin saat mutasi digital
            $table->enum('status', ['dikirim', 'diterima'])->default('dikirim');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_uang');
    }
};
