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
        Schema::create('pengaturan_ews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('barang_id');
            $table->unsignedBigInteger('cabang_id')->nullable()->comment('Null = Gudang Pusat');
            $table->integer('batas_ews')->default(0);
            $table->integer('hari_peramalan')->default(31);
            $table->timestamps();

            $table->foreign('barang_id')->references('id')->on('master_barang')->onDelete('cascade');
            $table->foreign('cabang_id')->references('id')->on('cabang')->onDelete('cascade');
            
            // Unik per cabang per barang
            $table->unique(['barang_id', 'cabang_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_ews');
    }
};
