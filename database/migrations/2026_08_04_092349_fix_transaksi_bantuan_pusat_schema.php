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
        Schema::table('transaksi_bantuan_pusat', function (Blueprint $table) {
            // Change enum to string so we don't violate sqlite CHECK constraint
            $table->string('status')->default('menunggu_pusat')->change();
            
            // Add proper foreign key
            $table->foreignId('pusat_aplikasi_layanan_id')->nullable()->constrained('pusat_aplikasi_layanan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_bantuan_pusat', function (Blueprint $table) {
            $table->dropForeign(['pusat_aplikasi_layanan_id']);
            $table->dropColumn('pusat_aplikasi_layanan_id');
        });
    }
};
