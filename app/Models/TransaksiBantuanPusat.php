<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiBantuanPusat extends Model
{
    use HasFactory;

    protected $table = 'transaksi_bantuan_pusat';

    protected $fillable = [
        'kasir_id',
        'cabang_id',
        'admin_id',
        'barang_id',
        'nomor_tujuan',
        'keterangan',
        'amount',
        'status',
        'harga_modal_pusat',
        'pusat_aplikasi_layanan_id',
        'fee_cabang',
        'total_bayar_pelanggan',
        'bukti_gambar',
    ];

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function apkLayananPusat(): BelongsTo
    {
        return $this->belongsTo(PusatAplikasiLayanan::class, 'apk_layanan_pusat_id');
    }
}
