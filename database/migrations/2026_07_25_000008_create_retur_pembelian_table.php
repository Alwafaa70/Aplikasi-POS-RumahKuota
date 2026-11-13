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
        Schema::create('retur_pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pembelian_id')->constrained('pembelian_supplier')->onDelete('cascade');
            $table->enum('status', ['menunggu_solusi', 'kirim_ulang', 'refund'])->default('menunggu_solusi');
            $table->date('tanggal_retur');
            $table->timestamps();
        });

        Schema::create('detail_retur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_id')->constrained('retur_pembelian')->onDelete('cascade');
            $table->foreignId('barang_fisik_id')->constrained('master_barang')->onDelete('cascade');
            $table->integer('qty_retur');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_retur');
        Schema::dropIfExists('retur_pembelian');
    }
};
