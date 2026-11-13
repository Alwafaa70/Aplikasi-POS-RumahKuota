<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabang extends Model
{
    use HasFactory;

    protected $table = 'cabang';

    protected $fillable = [
        'nama_cabang',
        'alamat',
        'area',
        'saldo_awal_cash',
        'saldo_cash_sekarang',
        'is_active',
    ];

    protected $casts = [
        'saldo_awal_cash' => 'decimal:2',
        'saldo_cash_sekarang' => 'decimal:2',
    ];

    public function aplikasiLayanan(): HasMany
    {
        return $this->hasMany(CabangAplikasiLayanan::class, 'cabang_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function kasCabang()
    {
        return $this->hasOne(KasCabang::class);
    }

    public function stokCabang(): HasMany
    {
        return $this->hasMany(StokCabang::class);
    }

    public function mutasiStokMasuk(): HasMany
    {
        return $this->hasMany(MutasiStok::class, 'cabang_tujuan_id');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    public function dataHistorisAgregasi(): HasMany
    {
        return $this->hasMany(DataHistorisAgregasi::class);
    }

    public function hasilPrediksiSes(): HasMany
    {
        return $this->hasMany(HasilPrediksiSes::class);
    }

    public function saldoApkLayanan(): HasMany
    {
        return $this->hasMany(SaldoApkLayanan::class);
    }
}
