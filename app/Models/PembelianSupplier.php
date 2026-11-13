<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembelianSupplier extends Model
{
    use HasFactory;

    protected $table = 'pembelian_supplier';

    protected $fillable = [
        'admin_id',
        'supplier_id',
        'total_harga',
        'metode_pembayaran',
        'apk_layanan_id',
        'status',
        'tanggal_pembelian',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function apkLayanan(): BelongsTo
    {
        return $this->belongsTo(ApkLayanan::class, 'apk_layanan_id');
    }

    public function detailPembelian(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'pembelian_id');
    }

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'pembelian_id');
    }
}
