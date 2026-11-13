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
        Schema::create('digital_b_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_digital_b_id')->constrained('produk_digital_b')->onDelete('cascade');
            $table->decimal('min_amount', 15, 2);
            $table->decimal('max_amount', 15, 2);
            $table->decimal('fee', 15, 2); // Biaya/fee untuk range ini
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_b_ranges');
    }
};
