<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdukDigitalB extends Model
{
    use HasFactory;

    protected $table = 'produk_digital_b';

    protected $fillable = [
        'nama_layanan',
    ];

    /**
     * Relasi One-to-Many ke DigitalBRange.
     * Satu layanan bisa memiliki banyak range fee.
     */
    public function ranges(): HasMany
    {
        return $this->hasMany(DigitalBRange::class, 'produk_digital_b_id');
    }
}
