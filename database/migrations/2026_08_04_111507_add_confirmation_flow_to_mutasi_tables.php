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
        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
            $table->string('bukti_asal')->nullable();
            $table->text('keterangan_asal')->nullable();
            $table->timestamp('waktu_konfirmasi_asal')->nullable();
            $table->foreignId('user_konfirmasi_asal_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('bukti_tujuan')->nullable();
            $table->text('keterangan_tujuan')->nullable();
            $table->timestamp('waktu_konfirmasi_tujuan')->nullable();
            $table->foreignId('user_konfirmasi_tujuan_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->text('alasan_tolak')->nullable();
        });

        Schema::table('mutasi_uang', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
            $table->string('bukti_asal')->nullable();
            $table->text('keterangan_asal')->nullable();
            $table->timestamp('waktu_konfirmasi_asal')->nullable();
            $table->foreignId('user_konfirmasi_asal_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('bukti_tujuan')->nullable();
            $table->text('keterangan_tujuan')->nullable();
            $table->timestamp('waktu_konfirmasi_tujuan')->nullable();
            $table->foreignId('user_konfirmasi_tujuan_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->text('alasan_tolak')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->dropForeign(['user_konfirmasi_asal_id']);
            $table->dropForeign(['user_konfirmasi_tujuan_id']);
            $table->dropColumn([
                'bukti_asal', 'keterangan_asal', 'waktu_konfirmasi_asal', 'user_konfirmasi_asal_id',
                'bukti_tujuan', 'keterangan_tujuan', 'waktu_konfirmasi_tujuan', 'user_konfirmasi_tujuan_id',
                'alasan_tolak'
            ]);
        });

        Schema::table('mutasi_uang', function (Blueprint $table) {
            $table->dropForeign(['user_konfirmasi_asal_id']);
            $table->dropForeign(['user_konfirmasi_tujuan_id']);
            $table->dropColumn([
                'bukti_asal', 'keterangan_asal', 'waktu_konfirmasi_asal', 'user_konfirmasi_asal_id',
                'bukti_tujuan', 'keterangan_tujuan', 'waktu_konfirmasi_tujuan', 'user_konfirmasi_tujuan_id',
                'alasan_tolak'
            ]);
        });
    }
};
