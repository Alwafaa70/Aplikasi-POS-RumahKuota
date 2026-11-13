<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailRequestOperasional extends Model
{
    use HasFactory;

    protected $table = 'detail_request_operasional';

    protected $fillable = [
        'request_id',
        'barang_fisik_id',
        'apk_layanan_id',
        'nominal_atau_qty',
    ];

    public function requestOperasional(): BelongsTo
    {
        return $this->belongsTo(RequestOperasional::class, 'request_id');
    }

    public function barangFisik(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_fisik_id');
    }

    public function apkLayanan(): BelongsTo
    {
        return $this->belongsTo(ApkLayanan::class, 'apk_layanan_id');
    }
}
