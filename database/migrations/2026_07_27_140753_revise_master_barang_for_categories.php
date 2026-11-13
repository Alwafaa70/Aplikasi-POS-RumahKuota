<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Karena data masih kosong atau prototype, kita modifikasi master_barang
        Schema::table('master_barang', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn('max_diskon');
            
            // Karena SQLite tidak mendukung alter column change/drop enum dengan mudah melalui Blueprint di versi lama,
            // Jika menggunakan MySQL, $table->dropColumn('kategori') bisa jalan.
            // Kita drop kolom kategori
            $table->dropColumn('kategori');
            
            // Tambahkan category_id & tipe item
            $table->unsignedBigInteger('category_id')->nullable()->after('harga_jual');
            $table->string('tipe_barang')->nullable()->after('category_id'); // Fisik ACC, Fisik Voucher, Digital A, Digital B
            
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
        
        // Hapus tabel digital yang lama karena sekarang dilebur
        Schema::dropIfExists('digital_b_ranges');
        Schema::dropIfExists('produk_digital_b');
        Schema::dropIfExists('produk_digital_a');
        
        // Buat tabel digital_b_ranges yang baru menunjuk ke master_barang_id
        Schema::create('digital_b_ranges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_barang_id');
            $table->decimal('min_nominal', 15, 2);
            $table->decimal('max_nominal', 15, 2);
            $table->decimal('fee', 15, 2);
            $table->timestamps();
            
            $table->foreign('master_barang_id')->references('id')->on('master_barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert logika (jika dibutuhkan, untuk purwarupa cukup lewati)
    }
};
