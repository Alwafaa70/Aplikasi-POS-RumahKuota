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
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('supplier')->onDelete('cascade');
            $table->foreignId('barang_fisik_id')->constrained('master_barang')->onDelete('cascade');
            $table->integer('qty_beli');
            $table->decimal('total_harga_beli', 15, 2);
            $table->string('kode_batch'); // Contoh: A200
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_masuk');
    }
};
