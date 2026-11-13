<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterAplikasiLayanan extends Model
{
    use HasFactory;

    protected $table = 'master_aplikasi_layanan';

    protected $fillable = [
        'nama_layanan',
        'jenis_layanan'
    ];

    public function cabangAplikasiLayanan()
    {
        return $this->hasMany(CabangAplikasiLayanan::class, 'master_aplikasi_layanan_id');
    }

    public function pusatAplikasiLayanan()
    {
        return $this->hasMany(PusatAplikasiLayanan::class, 'master_aplikasi_layanan_id');
    }
}
