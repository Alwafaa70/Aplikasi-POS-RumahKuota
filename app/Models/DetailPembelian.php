<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPembelian extends Model
{
    use HasFactory;

    protected $table = 'detail_pembelian';

    protected $fillable = [
        'pembelian_id',
        'barang_fisik_id',
        'qty',
        'harga_satuan',
        'subtotal',
    ];

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(PembelianSupplier::class, 'pembelian_id');
    }

    public function barangFisik(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_fisik_id');
    }
}
