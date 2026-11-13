<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaturanEws extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_ews';

    protected $fillable = [
        'barang_id',
        'cabang_id',
        'batas_ews',
        'safety_stock_percent',
        'hari_peramalan',
        'daily_forecast',
        'lead_time'
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }
}
