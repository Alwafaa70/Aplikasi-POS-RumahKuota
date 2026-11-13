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
        Schema::create('pembelian_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('supplier')->onDelete('cascade');
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['cash_pusat', 'apk_layanan'])->default('cash_pusat');
            $table->foreignId('apk_layanan_id')->nullable()->constrained('apk_layanan')->onDelete('set null');
            $table->enum('status', ['draft', 'dalam_perjalanan', 'selesai'])->default('draft');
            $table->date('tanggal_pembelian');
            $table->timestamps();
        });

        Schema::create('detail_pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->constrained('pembelian_supplier')->onDelete('cascade');
            $table->foreignId('barang_fisik_id')->constrained('master_barang')->onDelete('cascade');
            $table->integer('qty');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pembelian');
        Schema::dropIfExists('pembelian_supplier');
    }
};
