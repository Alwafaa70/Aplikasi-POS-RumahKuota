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
        Schema::table('pengaturan_ews', function (Blueprint $table) {
            $table->decimal('daily_forecast', 10, 2)->default(0)->after('batas_ews');
            $table->integer('lead_time')->default(7)->after('daily_forecast');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_ews', function (Blueprint $table) {
            $table->dropColumn(['daily_forecast', 'lead_time']);
        });
    }
};
