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
        Schema::table('transaksi', function (Blueprint $table) {
            $table->decimal('total_diskon', 15, 2)->default(0)->after('total_tagihan');
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->decimal('diskon', 15, 2)->default(0)->after('harga_jual_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('total_diskon');
        });

        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn('diskon');
        });
    }
};
