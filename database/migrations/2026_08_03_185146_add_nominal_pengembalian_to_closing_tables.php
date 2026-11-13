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
        Schema::table('laporan_closing', function (Blueprint $table) {
            $table->decimal('nominal_pengembalian', 15, 2)->default(0);
        });

        Schema::table('detail_closing_apk', function (Blueprint $table) {
            $table->decimal('nominal_pengembalian', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_closing', function (Blueprint $table) {
            $table->dropColumn('nominal_pengembalian');
        });

        Schema::table('detail_closing_apk', function (Blueprint $table) {
            $table->dropColumn('nominal_pengembalian');
        });
    }
};
