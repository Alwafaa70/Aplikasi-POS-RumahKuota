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
        Schema::table('detail_mutasi', function (Blueprint $table) {
            $table->integer('qty_diterima_baik')->default(0)->after('kuantitas');
            $table->integer('qty_rusak')->default(0)->after('qty_diterima_baik');
            $table->integer('qty_hilang')->default(0)->after('qty_rusak');
            $table->text('catatan_penerima')->nullable()->after('qty_hilang');
        });

        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->enum('status_penerimaan', ['sesuai', 'bermasalah'])->nullable()->after('status');
            $table->text('catatan_penerimaan')->nullable()->after('status_penerimaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_mutasi', function (Blueprint $table) {
            $table->dropColumn(['qty_diterima_baik', 'qty_rusak', 'qty_hilang', 'catatan_penerima']);
        });

        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->dropColumn(['status_penerimaan', 'catatan_penerimaan']);
        });
    }
};
