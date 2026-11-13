<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoApkLayanan extends Model
{
    use HasFactory;

    protected $table = 'saldo_apk_layanan';

    protected $fillable = [
        'cabang_id',
        'apk_layanan_id',
        'saldo',
    ];

    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function apkLayanan(): BelongsTo
    {
        return $this->belongsTo(ApkLayanan::class);
    }
}
