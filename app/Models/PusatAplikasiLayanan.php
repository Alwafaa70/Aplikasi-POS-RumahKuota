<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PusatAplikasiLayanan extends Model
{
    use HasFactory;

    protected $table = 'pusat_aplikasi_layanan';

    protected $fillable = [
        'master_aplikasi_layanan_id',
        'nomor_rekening',
        'saldo_awal',
        'saldo_sekarang',
        'is_qris_tujuan',
    ];

    protected $casts = [
        'saldo_awal' => 'decimal:2',
        'saldo_sekarang' => 'decimal:2',
    ];

    public function masterAplikasiLayanan()
    {
        return $this->belongsTo(MasterAplikasiLayanan::class, 'master_aplikasi_layanan_id');
    }
}
