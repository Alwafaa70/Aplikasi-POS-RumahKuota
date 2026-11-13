<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApkLayanan extends Model
{
    use HasFactory;

    protected $table = 'apk_layanan';

    protected $fillable = [
        'nama_layanan',
        'is_tujuan_qris_utama',
    ];

    protected function casts(): array
    {
        return [
            'is_tujuan_qris_utama' => 'boolean',
        ];
    }

    /**
     * Saldo apk layanan ini di semua cabang.
     */
    public function saldoPerCabang(): HasMany
    {
        return $this->hasMany(SaldoApkLayanan::class, 'apk_layanan_id');
    }
}

