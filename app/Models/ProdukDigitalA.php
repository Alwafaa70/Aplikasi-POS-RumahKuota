<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukDigitalA extends Model
{
    use HasFactory;

    protected $table = 'produk_digital_a';

    protected $fillable = [
        'nama_produk',
        'harga_jual',
    ];

    protected function casts(): array
    {
        return [
            'harga_jual' => 'decimal:2',
        ];
    }
}
