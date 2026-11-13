<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalBRange extends Model
{
    use HasFactory;

    protected $table = 'digital_b_ranges';

    protected $fillable = [
        'master_barang_id',
        'min_nominal',
        'max_nominal',
        'fee',
    ];

    protected function casts(): array
    {
        return [
            'min_nominal' => 'decimal:2',
            'max_nominal' => 'decimal:2',
            'fee' => 'decimal:2',
        ];
    }

    public function masterBarang(): BelongsTo
    {
        return $this->belongsTo(MasterBarang::class, 'master_barang_id');
    }
}
