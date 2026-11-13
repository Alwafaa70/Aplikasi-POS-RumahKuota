<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiUang extends Model
{
    use HasFactory;

    protected $table = 'mutasi_uang';

    protected $fillable = [
        'admin_id',
        'cabang_asal_id',
        'cabang_tujuan_id',
        'jenis_mutasi',
        'apk_sumber_id',
        'apk_tujuan_id',
        'nominal',
        'fee_admin',
        'status',
        'keterangan',
        'bukti_asal',
        'keterangan_asal',
        'waktu_konfirmasi_asal',
        'user_konfirmasi_asal_id',
        'bukti_tujuan',
        'keterangan_tujuan',
        'waktu_konfirmasi_tujuan',
        'user_konfirmasi_tujuan_id',
        'alasan_tolak'
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function cabangAsal(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_asal_id');
    }

    public function cabangTujuan(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_tujuan_id');
    }

    public function apkSumber(): BelongsTo
    {
        return $this->belongsTo(ApkLayanan::class, 'apk_sumber_id');
    }

    public function apkTujuan(): BelongsTo
    {
        return $this->belongsTo(ApkLayanan::class, 'apk_tujuan_id');
    }

    public function userKonfirmasiAsal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_konfirmasi_asal_id');
    }

    public function userKonfirmasiTujuan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_konfirmasi_tujuan_id');
    }
}
