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
        Schema::create('detail_mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mutasi_id')->constrained('mutasi_stok')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('master_barang')->onDelete('cascade');
            $table->integer('kuantitas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_mutasi');
    }
};
