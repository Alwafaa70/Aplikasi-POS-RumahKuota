<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestOperasional extends Model
{
    use HasFactory;

    protected $table = 'request_operasional';

    protected $fillable = [
        'kasir_id',
        'cabang_id',
        'jenis_request',
        'status',
        'keterangan',
        'tujuan_rekening',
        'nominal_request',
        'master_aplikasi_layanan_id',
        'admin_dompet_id',
        'biaya_admin_modal',
        'total_biaya_pelanggan',
        'status_request',
        'transaksi_id',
        'mutasi_id',
        'admin_id',
    ];

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailRequestOperasional::class, 'request_id');
    }

    public function masterAplikasiLayanan(): BelongsTo
    {
        return $this->belongsTo(MasterAplikasiLayanan::class, 'master_aplikasi_layanan_id');
    }

    public function adminDompet(): BelongsTo
    {
        return $this->belongsTo(PusatAplikasiLayanan::class, 'admin_dompet_id');
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}
