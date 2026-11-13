<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';

    protected $fillable = [
        'nama_supplier',
        'kontak',
    ];

    /**
     * Relasi One-to-Many ke BarangMasuk.
     * Satu supplier bisa memiliki banyak catatan barang masuk.
     */
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class, 'supplier_id');
    }
}
