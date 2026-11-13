<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MutasiStok extends Model
{
    use HasFactory;

    protected $table = 'mutasi_stok';

    protected $fillable = [
        'admin_id',
        'cabang_asal_id',
        'cabang_tujuan_id',
        'status',
        'tanggal_mutasi',
        'catatan_penerimaan',
        'status_penerimaan',
        'bukti_gambar',
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

    public function detailMutasi(): HasMany
    {
        return $this->hasMany(DetailMutasi::class, 'mutasi_id');
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
