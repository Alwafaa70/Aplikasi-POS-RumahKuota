<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokCabangBatch extends Model
{
    use HasFactory;

    protected $table = 'stok_cabang_batches';

    protected $fillable = [
        'cabang_id',
        'barang_id',
        'barang_masuk_id',
        'stok_sisa_cabang',
        'hpp_batch'
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class, 'barang_masuk_id');
    }
}
