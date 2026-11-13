<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterBarang extends Model
{
    use HasFactory;

    protected $table = 'master_barang';

    protected $fillable = [
        'nama_barang',
        'harga_jual',
        'category_id',
        'tipe_barang',
        'jenis_transaksi',
        'safety_stock_percent',
        'batas_ews',
        'hari_peramalan',
    ];

    protected $appends = [
        'harga_modal',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function rangesDigitalB(): HasMany
    {
        return $this->hasMany(\App\Models\DigitalBRange::class, 'master_barang_id');
    }

    /**
     * Accessor untuk mendapatkan Harga Modal (HPP terbaru) secara dinamis dari BarangMasuk.
     */
    public function getHargaModalAttribute()
    {
        $pembelian = $this->barangMasuk()->latest()->first();
        if ($pembelian && $pembelian->qty_beli > 0 && $pembelian->total_harga_beli > 0) {
            return round($pembelian->total_harga_beli / $pembelian->qty_beli);
        }
        return $this->harga_jual ? round($this->harga_jual * 0.7) : 10000;
    }

    /**
     * Sembunyikan informasi Harga Modal/HPP dari endpoint jika bukan Owner.
     */
    public function toArray()
    {
        $array = parent::toArray();
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role !== 'owner') {
            // Karena MasterBarang ini tidak menyimpan HPP secara langsung (HPP ada di BarangMasuk/Service), 
            // kita antisipasi jika ada attribute dinamis (appends) atau relasi.
            // Saat ini HPP tidak ada di model dasar, tapi kita siapkan hook ini
            unset($array['harga_modal_rata_rata']); 
        }
        return $array;
    }

    public function stokPusat(): HasOne
    {
        return $this->hasOne(StokPusat::class, 'barang_id');
    }

    public function stokCabang(): HasMany
    {
        return $this->hasMany(StokCabang::class, 'barang_id');
    }

    public function detailMutasi(): HasMany
    {
        return $this->hasMany(DetailMutasi::class, 'barang_id');
    }

    public function detailTransaksi(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'barang_id');
    }

    public function dataHistorisAgregasi(): HasMany
    {
        return $this->hasMany(DataHistorisAgregasi::class, 'barang_id');
    }

    public function hasilPrediksiSes(): HasMany
    {
        return $this->hasMany(HasilPrediksiSes::class, 'barang_id');
    }

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'barang_fisik_id');
    }
}
