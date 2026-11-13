<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetoranDompetPusat extends Model
{
    use HasFactory;

    protected $fillable = [
        'pusat_aplikasi_layanan_id',
        'nominal',
        'jenis_pengaju',
        'status',
        'catatan_pengaju',
        'catatan_konfirmasi',
        'bukti_transfer',
        'admin_id'
    ];

    public function dompet()
    {
        return $this->belongsTo(PusatAplikasiLayanan::class, 'pusat_aplikasi_layanan_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
