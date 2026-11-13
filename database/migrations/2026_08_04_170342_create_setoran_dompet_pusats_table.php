<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setoran_dompet_pusats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pusat_aplikasi_layanan_id')->nullable()->constrained('pusat_aplikasi_layanan')->onDelete('cascade');
            $table->decimal('nominal', 15, 2);
            $table->enum('jenis_pengaju', ['owner', 'admin']);
            $table->enum('status', ['pending_admin', 'pending_owner', 'selesai', 'ditolak', 'dibatalkan'])->default('pending_admin');
            $table->text('catatan_pengaju')->nullable();
            $table->text('catatan_konfirmasi')->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setoran_dompet_pusats');
    }
};
