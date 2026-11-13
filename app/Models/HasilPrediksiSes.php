<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HasilPrediksiSes extends Model
{
    use HasFactory;

    protected $table = 'hasil_prediksi_ses';

    protected $fillable = [
        'cabang_id',
        'barang_id',
        'periode_prediksi',
        'angka_rekomendasi',
        'prediksi_laba',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }
}
