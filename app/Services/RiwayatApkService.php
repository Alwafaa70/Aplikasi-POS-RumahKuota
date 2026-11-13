<?php

namespace App\Services;

use App\Models\RiwayatAplikasiLayanan;
use App\Models\PusatAplikasiLayanan;
use App\Models\CabangAplikasiLayanan;

class RiwayatApkService
{
    /**
     * Mencatat riwayat mutasi APK.
     *
     * @param string $jenisApk 'pusat' atau 'cabang'
     * @param int $apkLayananId ID PusatAplikasiLayanan atau CabangAplikasiLayanan
     * @param string $jenisMutasi 'masuk' atau 'keluar'
     * @param float $nominal
     * @param string $keterangan
     * @param object|null $referensi Model transaksi/mutasi
     */
    public static function catat($jenisApk, $apkLayananId, $jenisMutasi, $nominal, $keterangan, $referensi = null)
    {
        // Hitung saldo akhir berdasarkan jenis_apk
        $saldoAkhir = 0;
        if ($jenisApk === 'pusat') {
            $apk = PusatAplikasiLayanan::find($apkLayananId);
            $saldoAkhir = $apk ? $apk->saldo_sekarang : 0;
        } else {
            $apk = CabangAplikasiLayanan::find($apkLayananId);
            $saldoAkhir = $apk ? $apk->saldo_sekarang : 0;
        }

        RiwayatAplikasiLayanan::create([
            'jenis_apk' => $jenisApk,
            'apk_layanan_id' => $apkLayananId,
            'jenis_mutasi' => $jenisMutasi,
            'nominal' => $nominal,
            'saldo_akhir' => $saldoAkhir,
            'keterangan' => $keterangan,
            'referensi_id' => $referensi ? $referensi->id : null,
            'referensi_type' => $referensi ? get_class($referensi) : null,
        ]);
    }
}
