<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetoranClosing extends Model
{
    protected $fillable = [
        'laporan_closing_id',
        'cabang_id',
        'pusat_aplikasi_layanan_id',
        'nominal',
        'status',
        'dikonfirmasi_oleh',
        'waktu_dikonfirmasi',
        'catatan',
        'bukti_foto'
    ];

    public function laporanClosing()
    {
        return $this->belongsTo(LaporanClosing::class, 'laporan_closing_id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function pusatAplikasiLayanan()
    {
        return $this->belongsTo(PusatAplikasiLayanan::class, 'pusat_aplikasi_layanan_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }
}
