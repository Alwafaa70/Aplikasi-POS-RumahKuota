<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CabangAplikasiLayanan extends Model
{
    use HasFactory;

    protected $table = 'cabang_aplikasi_layanan';

    protected $fillable = [
        'cabang_id',
        'master_aplikasi_layanan_id',
        'nomor_rekening',
        'saldo_awal',
        'saldo_sekarang',
        'is_qris_tujuan',
    ];

    protected $casts = [
        'is_qris_tujuan' => 'boolean',
        'saldo_awal' => 'decimal:2',
        'saldo_sekarang' => 'decimal:2',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function masterAplikasiLayanan()
    {
        return $this->belongsTo(MasterAplikasiLayanan::class, 'master_aplikasi_layanan_id');
    }
}
