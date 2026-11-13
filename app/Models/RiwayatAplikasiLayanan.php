<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RiwayatAplikasiLayanan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_aplikasi_layanan';

    protected $fillable = [
        'jenis_apk',
        'apk_layanan_id',
        'jenis_mutasi',
        'nominal',
        'saldo_akhir',
        'keterangan',
        'referensi_id',
        'referensi_type',
    ];

    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }

    // Helper method to get the related APK model
    public function getApkModel()
    {
        if ($this->jenis_apk === 'pusat') {
            return PusatAplikasiLayanan::find($this->apk_layanan_id);
        }
        return CabangAplikasiLayanan::find($this->apk_layanan_id);
    }
}
