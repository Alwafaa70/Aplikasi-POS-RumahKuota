<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataHistorisAgregasi extends Model
{
    use HasFactory;

    protected $table = 'data_historis_agregasi';

    protected $fillable = [
        'cabang_id',
        'barang_id',
        'periode_bulan',
        'total_unit_terjual',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
