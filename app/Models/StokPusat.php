<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokPusat extends Model
{
    use HasFactory;

    protected $table = 'stok_pusat';

    protected $fillable = [
        'barang_id',
        'jumlah_stok',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
