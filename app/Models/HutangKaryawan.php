<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HutangKaryawan extends Model
{
    protected $fillable = [
        'user_id',
        'laporan_closing_id',
        'nominal',
        'status',
        'keterangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function laporanClosing()
    {
        return $this->belongsTo(LaporanClosing::class, 'laporan_closing_id');
    }
}
