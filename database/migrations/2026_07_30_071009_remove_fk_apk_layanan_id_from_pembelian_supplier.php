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
        Schema::table('pembelian_supplier', function (Blueprint $table) {
            $table->dropForeign(['apk_layanan_id']);
            // Drop column and recreate it as a regular integer to avoid FK constraints completely
            $table->dropColumn('apk_layanan_id');
        });

        Schema::table('pembelian_supplier', function (Blueprint $table) {
            // Recreate it as an integer without FK constraints (nullable)
            $table->unsignedBigInteger('apk_layanan_id')->nullable()->after('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelian_supplier', function (Blueprint $table) {
            $table->dropColumn('apk_layanan_id');
        });
        
        Schema::table('pembelian_supplier', function (Blueprint $table) {
            $table->foreignId('apk_layanan_id')->nullable()->constrained('apk_layanan')->onDelete('set null');
        });
    }
};
