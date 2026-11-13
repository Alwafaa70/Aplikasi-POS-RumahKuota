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
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->decimal('batas_diskon', 15, 2)->default(0)->after('total_harga_beli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_masuk', function (Blueprint $table) {
            $table->dropColumn('batas_diskon');
        });
    }
};
