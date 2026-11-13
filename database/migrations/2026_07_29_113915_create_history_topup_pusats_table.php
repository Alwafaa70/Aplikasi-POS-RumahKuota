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
        Schema::create('history_topup_pusats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pusat_aplikasi_layanan_id')->constrained('pusat_aplikasi_layanan')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->decimal('nominal', 15, 2);
            $table->string('bukti_foto');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_topup_pusats');
    }
};
