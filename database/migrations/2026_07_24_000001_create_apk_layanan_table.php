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
        Schema::create('apk_layanan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_layanan'); // Contoh: Bank Mandiri, BCA, dll
            $table->boolean('is_tujuan_qris_utama')->default(false); // Wajib minimal 1 true
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apk_layanan');
    }
};
