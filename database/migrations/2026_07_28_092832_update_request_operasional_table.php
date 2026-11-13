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
        // First we modify the enum to include new types if using SQLite by dropping and recreating or just by letting Laravel handle string. SQLite enums are just strings.
        // Wait, SQLite doesn't support modifying enum columns directly. We just add new columns.
        Schema::table('request_operasional', function (Blueprint $table) {
            $table->string('tujuan_rekening')->nullable()->after('jenis_request');
            $table->decimal('nominal_request', 15, 2)->nullable()->after('tujuan_rekening');
            $table->foreignId('master_aplikasi_layanan_id')->nullable()->constrained('master_aplikasi_layanan')->onDelete('set null')->after('nominal_request');
            $table->foreignId('admin_dompet_id')->nullable()->constrained('pusat_aplikasi_layanan')->onDelete('set null')->after('master_aplikasi_layanan_id');
            $table->decimal('biaya_admin_modal', 15, 2)->nullable()->after('admin_dompet_id');
            $table->decimal('total_biaya_pelanggan', 15, 2)->nullable()->after('biaya_admin_modal');
            $table->string('status_request')->default('menunggu_admin')->after('status');
            $table->foreignId('transaksi_id')->nullable()->constrained('transaksi')->onDelete('set null')->after('status_request');
            $table->unsignedBigInteger('mutasi_id')->nullable()->after('transaksi_id');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null')->after('mutasi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_operasional', function (Blueprint $table) {
            $table->dropForeign(['master_aplikasi_layanan_id']);
            $table->dropForeign(['admin_dompet_id']);
            $table->dropForeign(['transaksi_id']);
            $table->dropForeign(['admin_id']);
            $table->dropColumn([
                'tujuan_rekening', 'nominal_request', 'master_aplikasi_layanan_id', 
                'admin_dompet_id', 'biaya_admin_modal', 'total_biaya_pelanggan', 
                'status_request', 'transaksi_id', 'mutasi_id', 'admin_id'
            ]);
        });
    }
};
