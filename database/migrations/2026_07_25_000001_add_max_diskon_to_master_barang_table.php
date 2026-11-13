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
        Schema::table('master_barang', function (Blueprint $table) {
            $table->decimal('max_diskon', 15, 2)->nullable()->after('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_barang', function (Blueprint $table) {
            $table->dropColumn('max_diskon');
        });
    }
};
