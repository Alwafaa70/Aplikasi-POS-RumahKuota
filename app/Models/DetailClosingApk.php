<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailClosingApk extends Model
{
    use HasFactory;

    protected $table = 'detail_closing_apk';

    protected $fillable = [
        'closing_id',
        'apk_layanan_id',
        'saldo_aktual',
        'saldo_sistem',
        'selisih',
        'status',
        'bukti_saldo',
        'status_pengembalian_minus',
        'catatan_pengembalian',
        'nominal_pengembalian',
    ];

    public function masterAplikasiLayanan(): BelongsTo
    {
        return $this->belongsTo(MasterAplikasiLayanan::class, 'apk_layanan_id');
    }

    public function laporanClosing(): BelongsTo
    {
        return $this->belongsTo(LaporanClosing::class, 'closing_id');
    }
}
