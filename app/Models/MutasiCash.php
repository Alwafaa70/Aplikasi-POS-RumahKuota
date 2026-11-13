<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiCash extends Model
{
    protected $table = 'mutasi_cash';
    protected $fillable = [
        'asal_type', 'asal_cabang_id', 'tujuan_cabang_id', 'nominal', 'status', 'keterangan', 'admin_id'
    ];

    public function asalCabang(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'asal_cabang_id');
    }

    public function tujuanCabang(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'tujuan_cabang_id');
    }

    public function admin(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
