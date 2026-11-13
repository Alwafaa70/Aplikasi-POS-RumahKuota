<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';

    protected $fillable = [
        'transaksi_id',
        'barang_id',
        'harga_jual_final',
        'kuantitas',
        'hpp_satuan',
        'jenis_item',
        'diskon',
        'cabang_apk_layanan_id',
        'pusat_apk_layanan_id',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function cabangApkLayanan(): BelongsTo
    {
        return $this->belongsTo(CabangAplikasiLayanan::class, 'cabang_apk_layanan_id');
    }

    public function pusatApkLayanan(): BelongsTo
    {
        return $this->belongsTo(PusatAplikasiLayanan::class, 'pusat_apk_layanan_id');
    }
}
