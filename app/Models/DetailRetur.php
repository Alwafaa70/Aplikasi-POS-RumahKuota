<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailRetur extends Model
{
    use HasFactory;

    protected $table = 'detail_retur';

    protected $fillable = [
        'retur_id',
        'barang_fisik_id',
        'qty_retur',
        'keterangan',
    ];

    public function returPembelian(): BelongsTo
    {
        return $this->belongsTo(ReturPembelian::class, 'retur_id');
    }

    public function barangFisik(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_fisik_id');
    }
}
