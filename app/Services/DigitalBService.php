<?php

namespace App\Services;

use App\Models\DigitalBRange;
use App\Models\MasterBarang;

class DigitalBService
{
    /**
     * Validasi nominal transaksi pelanggan untuk Produk Digital B (Range Fee).
     */
    public function validasiDanCariFee(int $produkDigitalBId, float $amount): array
    {
        // Ambil produk digital B (dari master_barang) beserta semua range-nya (urut berdasarkan min_nominal)
        $produk = MasterBarang::with(['rangesDigitalB' => function ($query) {
            $query->orderBy('min_nominal', 'asc');
        }])->find($produkDigitalBId);

        // Validasi: Produk tidak ditemukan
        if (!$produk || $produk->tipe_barang !== 'Digital B') {
            return [
                'success' => false,
                'fee'     => null,
                'message' => 'Produk Digital B tidak ditemukan.',
            ];
        }

        // Validasi: Belum ada range yang dikonfigurasi
        if ($produk->rangesDigitalB->isEmpty()) {
            return [
                'success' => false,
                'fee'     => null,
                'message' => "Layanan '{$produk->nama_barang}' belum memiliki konfigurasi range fee.",
            ];
        }

        // Ambil batas terendah dan tertinggi
        $rangeTerendah  = $produk->rangesDigitalB->first();
        $rangeTertinggi = $produk->rangesDigitalB->last();

        // Validasi: Amount di bawah minimum
        if ($amount < $rangeTerendah->min_nominal) {
            return [
                'success'    => false,
                'fee'        => null,
                'message'    => "Nominal Rp " . number_format($amount, 0, ',', '.') .
                               " di bawah minimum transaksi Rp " .
                               number_format($rangeTerendah->min_nominal, 0, ',', '.') . ".",
                'min_amount' => (float) $rangeTerendah->min_nominal,
            ];
        }

        // Validasi: Amount melebihi max_nominal di range tertinggi (LIMIT TRANSAKSI)
        if ($amount > $rangeTertinggi->max_nominal) {
            return [
                'success'    => false,
                'fee'        => null,
                'message'    => "Nominal Rp " . number_format($amount, 0, ',', '.') .
                               " melebihi batas maksimum transaksi Rp " .
                               number_format($rangeTertinggi->max_nominal, 0, ',', '.') .
                               ". Transaksi ditolak.",
                'max_amount' => (float) $rangeTertinggi->max_nominal,
            ];
        }

        // Cari fee yang sesuai berdasarkan range
        $matchedRange = $produk->rangesDigitalB
            ->where('min_nominal', '<=', $amount)
            ->where('max_nominal', '>=', $amount)
            ->first();

        if (!$matchedRange) {
            return [
                'success' => false,
                'fee'     => null,
                'message' => "Tidak ditemukan range fee untuk nominal Rp " .
                             number_format($amount, 0, ',', '.') . ".",
            ];
        }

        // Berhasil: Return fee yang sesuai
        return [
            'success'      => true,
            'fee'          => (float) $matchedRange->fee,
            'message'      => "Fee untuk nominal Rp " . number_format($amount, 0, ',', '.') .
                             " adalah Rp " . number_format($matchedRange->fee, 0, ',', '.') . ".",
            'nama_layanan' => $produk->nama_barang,
            'range'        => [
                'min_amount' => (float) $matchedRange->min_nominal,
                'max_amount' => (float) $matchedRange->max_nominal,
            ],
        ];
    }

    /**
     * Ambil semua range fee untuk suatu produk Digital B.
     */
    public function getRanges(int $produkDigitalBId): array
    {
        $produk = MasterBarang::with(['rangesDigitalB' => function ($query) {
            $query->orderBy('min_nominal', 'asc');
        }])->find($produkDigitalBId);

        if (!$produk || $produk->tipe_barang !== 'Digital B') {
            return [
                'success' => false,
                'message' => 'Produk Digital B tidak ditemukan.',
                'ranges'  => [],
            ];
        }

        return [
            'success'      => true,
            'nama_layanan' => $produk->nama_barang,
            'ranges'       => $produk->rangesDigitalB->map(function ($range) {
                return [
                    'id'         => $range->id,
                    'min_amount' => (float) $range->min_nominal,
                    'max_amount' => (float) $range->max_nominal,
                    'fee'        => (float) $range->fee,
                ];
            })->toArray(),
        ];
    }
}
