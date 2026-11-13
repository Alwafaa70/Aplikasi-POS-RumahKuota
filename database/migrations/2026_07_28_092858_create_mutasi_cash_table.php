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
        Schema::create('mutasi_cash', function (Blueprint $table) {
            $table->id();
            $table->enum('asal_type', ['pusat', 'cabang']);
            $table->foreignId('asal_cabang_id')->nullable()->constrained('cabang')->onDelete('cascade');
            $table->foreignId('tujuan_cabang_id')->constrained('cabang')->onDelete('cascade');
            $table->decimal('nominal', 15, 2);
            $table->string('status')->default('dalam_pengiriman'); // dalam_pengiriman, diterima, ditolak
            $table->text('keterangan')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_cash');
    }
};
