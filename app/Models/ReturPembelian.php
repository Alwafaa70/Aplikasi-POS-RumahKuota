<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturPembelian extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian';

    protected $fillable = [
        'admin_id',
        'pembelian_id',
        'status',
        'tanggal_retur',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(PembelianSupplier::class, 'pembelian_id');
    }

    public function detailRetur(): HasMany
    {
        return $this->hasMany(DetailRetur::class, 'retur_id');
    }
}
