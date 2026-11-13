<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailMutasi extends Model
{
    use HasFactory;

    protected $table = 'detail_mutasi';

    protected $fillable = [
        'mutasi_id',
        'barang_id',
        'kuantitas',
        'barang_masuk_id',
        'hpp_satuan'
    ];

    public function mutasiStok(): BelongsTo
    {
        return $this->belongsTo(MutasiStok::class, 'mutasi_id');
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
