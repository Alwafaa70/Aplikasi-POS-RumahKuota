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
        Schema::create('produk_digital_a', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk'); // Contoh: Pulsa 20k, Diamond FF
            $table->decimal('harga_jual', 15, 2); // Harga jual tetap (fixed)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk_digital_a');
    }
};
