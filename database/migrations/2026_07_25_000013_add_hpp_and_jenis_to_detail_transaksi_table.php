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
        Schema::table('detail_transaksi', function (Blueprint $table) {
            // Nullable karena data lama mungkin tidak ada hpp atau jenis_item
            $table->decimal('hpp_satuan', 15, 2)->nullable()->after('barang_id');
            $table->string('jenis_item')->default('barang_fisik')->after('hpp_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn('hpp_satuan');
            $table->dropColumn('jenis_item');
        });
    }
};
