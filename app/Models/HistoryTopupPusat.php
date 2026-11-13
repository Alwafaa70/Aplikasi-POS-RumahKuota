<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryTopupPusat extends Model
{
    protected $fillable = [
        'pusat_aplikasi_layanan_id',
        'admin_id',
        'nominal',
        'bukti_foto',
        'catatan'
    ];

    public function dompetPusat()
    {
        return $this->belongsTo(PusatAplikasiLayanan::class, 'pusat_aplikasi_layanan_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
