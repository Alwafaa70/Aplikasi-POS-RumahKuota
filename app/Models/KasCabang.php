<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasCabang extends Model
{
    use HasFactory;

    protected $table = 'kas_cabang';

    protected $fillable = [
        'cabang_id',
        'saldo',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }
}
