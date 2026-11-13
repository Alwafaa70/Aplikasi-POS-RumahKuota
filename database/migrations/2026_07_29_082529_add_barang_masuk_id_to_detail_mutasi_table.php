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
            $table->unsignedBigInteger('barang_masuk_id')->nullable()->after('barang_id');
            $table->decimal('hpp_satuan', 15, 2)->default(0)->after('jumlah');
            
            $table->foreign('barang_masuk_id')->references('id')->on('barang_masuk')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_mutasi', function (Blueprint $table) {
            $table->dropForeign(['barang_masuk_id']);
            $table->dropColumn(['barang_masuk_id', 'hpp_satuan']);
        });
    }
};
