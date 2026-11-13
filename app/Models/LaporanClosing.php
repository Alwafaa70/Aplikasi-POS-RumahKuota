<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanClosing extends Model
{
    use HasFactory;

    protected $table = 'laporan_closing';

    protected $fillable = [
        'kasir_id',
        'cabang_id',
        'tanggal',
        'uang_fisik_aktual',
        'uang_fisik_sistem',
        'selisih_fisik',
        'status_fisik',
        'catatan_kasir',
        'bukti_uang_fisik',
        'status_konfirmasi',
        'catatan_admin',
        'status_pengembalian_minus',
        'catatan_pengembalian',
        'nominal_pengembalian',
    ];

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailClosingApk::class, 'closing_id');
    }
}
