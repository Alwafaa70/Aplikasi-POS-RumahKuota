<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuk';

    protected $fillable = [
        'pembelian_id',
        'supplier_id',
        'barang_fisik_id',
        'qty_beli',
        'stok_sisa',
        'total_harga_beli',
        'batas_diskon',
        'kode_batch',
    ];

    /**
     * Sembunyikan informasi Harga Modal/HPP dari endpoint jika bukan Owner.
     */
    public function toArray()
    {
        $array = parent::toArray();
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role !== 'owner') {
            unset($array['total_harga_beli']);
            // Kasir/Admin Pusat juga tidak boleh melihat stok sisa dari batch secara mendetail jika itu mengungkapkan HPP secara tidak langsung,
            // namun qty_beli dan stok_sisa mungkin dibutuhkan sistem. Kita hide total harga belinya saja.
        }
        return $array;
    }

    protected function casts(): array
    {
        return [
            'total_harga_beli' => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Supplier.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi ke MasterBarang (barang fisik).
     */
    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_fisik_id');
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(PembelianSupplier::class, 'pembelian_id');
    }
}
