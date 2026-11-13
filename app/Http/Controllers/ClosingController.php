<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanClosing;
use App\Models\DetailClosingApk;
use App\Models\KasCabang;
use App\Models\SaldoApkLayanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClosingController extends Controller
{
    /**
     * Tampilkan form Blind Closing untuk Kasir.
     */
    public function index()
    {
        // Kasir menginput manual tanpa tahu saldo sistem
        $apks = \App\Models\CabangAplikasiLayanan::with('masterAplikasiLayanan')
                ->where('cabang_id', Auth::user()->cabang_id)
                ->get();
                
        return view('kasir.closing.index', compact('apks'));
    }

    /**
     * Submit Blind Closing.
     */
    public function store(Request $request)
    {
        $request->validate([
            'uang_fisik_aktual' => 'required|numeric|min:0',
            'saldo_apk'         => 'array',
            'saldo_apk.*'       => 'numeric|min:0',
            'catatan'           => 'nullable|string',
            'bukti_uang_fisik'  => 'nullable|image|max:5120', // Max 5MB
            'bukti_saldo_apk.*' => 'nullable|image|max:5120',
        ]);

        $user = Auth::user();
        $cabangId = $user->cabang_id;

        DB::beginTransaction();
        try {
            $cabang = \App\Models\Cabang::lockForUpdate()->find($cabangId);
            // Ambil uang fisik sistem saat ini
            $kasSistem = $cabang->saldo_cash_sekarang ?? 0;
            $uangAktual = $request->uang_fisik_aktual;
            $selisihFisik = $uangAktual - $kasSistem;

            $statusFisik = 'balance';
            $minusDetails = [];

            if ($selisihFisik > 0) $statusFisik = 'plus';
            elseif ($selisihFisik < 0) {
                $statusFisik = 'minus';
                $minusDetails[] = "Cash Laci Minus Rp " . number_format(abs($selisihFisik), 0, ',', '.');
            }

            $buktiUangFisikPath = null;
            if ($request->hasFile('bukti_uang_fisik')) {
                $buktiUangFisikPath = $request->file('bukti_uang_fisik')->store('closing_bukti', 'public');
            }

            $closing = LaporanClosing::create([
                'kasir_id'          => $user->id,
                'cabang_id'         => $cabangId,
                'tanggal'           => now()->toDateString(),
                'uang_fisik_aktual' => $uangAktual,
                'uang_fisik_sistem' => $kasSistem,
                'selisih_fisik'     => $selisihFisik,
                'status_fisik'      => $statusFisik,
                'catatan_kasir'     => $request->catatan,
                'bukti_uang_fisik'  => $buktiUangFisikPath,
                'status_konfirmasi' => 'menunggu_konfirmasi',
            ]);

            // Jika ada uang fisik yang dilaporkan, buat setoran (dikurangi saldo awal untuk besok jika ada)
            // Asumsi: Semua uang aktual (kecuali modal awal laci) disetorkan. 
            // Untuk sementara, kita setorkan seluruh uang aktual dikurangi modal awal laci cabang
            $modalAwal = $cabang->saldo_awal_cash ?? 0;
            $nominalSetor = max(0, $uangAktual - $modalAwal);
            
            if ($nominalSetor > 0) {
                \App\Models\SetoranClosing::create([
                    'laporan_closing_id' => $closing->id,
                    'cabang_id' => $cabangId,
                    'nominal' => $nominalSetor,
                    'status' => 'menunggu_konfirmasi',
                ]);
            }

            // Jika minus, catat sebagai hutang karyawan
            if ($statusFisik === 'minus') {
                \App\Models\HutangKaryawan::create([
                    'user_id' => $user->id,
                    'laporan_closing_id' => $closing->id,
                    'nominal' => abs($selisihFisik),
                    'status' => 'belum_lunas',
                    'keterangan' => 'Minus closing shift pada tanggal ' . now()->toDateString()
                ]);
            }

            if ($request->has('saldo_apk')) {
                foreach ($request->saldo_apk as $apkId => $saldoAktual) {
                    $saldoSistem = \App\Models\CabangAplikasiLayanan::where('cabang_id', $cabangId)
                                    ->where('master_aplikasi_layanan_id', $apkId)
                                    ->value('saldo_sekarang') ?? 0;
                    
                    $selisihApk = $saldoAktual - $saldoSistem;
                    $statusApk = 'balance';
                    if ($selisihApk > 0) $statusApk = 'plus';
                    elseif ($selisihApk < 0) {
                        $statusApk = 'minus';
                        $namaLayanan = \App\Models\MasterAplikasiLayanan::find($apkId)->nama_layanan ?? 'Aplikasi';
                        $minusDetails[] = "Saldo $namaLayanan Minus Rp " . number_format(abs($selisihApk), 0, ',', '.');
                    }

                    $buktiSaldoPath = null;
                    if ($request->hasFile("bukti_saldo_apk.{$apkId}")) {
                        $buktiSaldoPath = $request->file("bukti_saldo_apk.{$apkId}")->store('closing_bukti_apk', 'public');
                    }

                    DetailClosingApk::create([
                        'closing_id'     => $closing->id,
                        'apk_layanan_id' => $apkId,
                        'saldo_aktual'   => $saldoAktual,
                        'saldo_sistem'   => $saldoSistem,
                        'selisih'        => $selisihApk,
                        'status'         => $statusApk,
                        'bukti_saldo'    => $buktiSaldoPath,
                    ]);
                }
            }
            
            // Reset Saldo Laci Kasir Cabang kembali ke Modal Awal
            $cabang->update(['saldo_cash_sekarang' => $modalAwal]);

            DB::commit();

            if (count($minusDetails) > 0) {
                return redirect()->route('kasir.dashboard')->with('success', 'Laporan Closing berhasil disubmit. Kas Laci di-reset ke modal awal.')->with('has_minus', true)->with('minus_details', $minusDetails);
            }

            return redirect()->route('kasir.dashboard')->with('success', 'Laporan Closing berhasil disubmit. Kas Laci di-reset ke modal awal.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal submit closing: ' . $e->getMessage());
        }
    }

    public function riwayatMinus()
    {
        $user = Auth::user();
        $cabangId = $user->cabang_id;

        $minusFisik = LaporanClosing::where('cabang_id', $cabangId)
            ->where('status_fisik', 'minus')
            ->orderBy('created_at', 'desc')
            ->get();

        $minusApk = \App\Models\DetailClosingApk::whereHas('laporanClosing', function($q) use ($cabangId) {
            $q->where('cabang_id', $cabangId);
        })
        ->where('status', 'minus')
        ->orderBy('created_at', 'desc')
        ->get();

        return view('kasir.closing.minus', compact('minusFisik', 'minusApk'));
    }

    public function reimburse(Request $request, $id, $type)
    {
        $request->validate([
            'nominal_pengembalian' => 'required|numeric|min:1',
            'catatan_pengembalian' => 'required|string|max:255'
        ]);

        if ($type == 'fisik') {
            $laporan = LaporanClosing::findOrFail($id);
            if ($laporan->status_konfirmasi === 'dikonfirmasi') {
                return back()->with('error', 'Laporan sudah dikonfirmasi admin. Tidak bisa mengembalikan uang lagi.');
            }
            $totalMinus = abs($laporan->selisih_fisik);
            $newNominal = ($laporan->nominal_pengembalian ?? 0) + $request->nominal_pengembalian;
            
            if ($newNominal > $totalMinus) {
                return back()->with('error', 'Nominal pengembalian melebihi sisa minus.');
            }

            $isLunas = $newNominal >= $totalMinus;

            $laporan->update([
                'nominal_pengembalian' => $newNominal,
                'status_pengembalian_minus' => $isLunas ? 'dikembalikan' : 'belum',
                'catatan_pengembalian' => $request->catatan_pengembalian
            ]);

            // Tutup hutang karyawan jika ada dan sudah lunas
            if ($isLunas) {
                $hutang = \App\Models\HutangKaryawan::where('laporan_closing_id', $laporan->id)->where('status', 'belum_lunas')->first();
                if ($hutang) {
                    $hutang->update(['status' => 'lunas']);
                }
            }
        } elseif ($type == 'apk') {
            $detail = \App\Models\DetailClosingApk::with('laporanClosing')->findOrFail($id);
            if ($detail->laporanClosing->status_konfirmasi === 'dikonfirmasi') {
                return back()->with('error', 'Laporan sudah dikonfirmasi admin. Tidak bisa mengembalikan saldo lagi.');
            }
            $totalMinus = abs($detail->selisih);
            $newNominal = ($detail->nominal_pengembalian ?? 0) + $request->nominal_pengembalian;

            if ($newNominal > $totalMinus) {
                return back()->with('error', 'Nominal pengembalian melebihi sisa minus.');
            }

            $isLunas = $newNominal >= $totalMinus;

            $detail->update([
                'nominal_pengembalian' => $newNominal,
                'status_pengembalian_minus' => $isLunas ? 'dikembalikan' : 'belum',
                'catatan_pengembalian' => $request->catatan_pengembalian
            ]);
        }

        return back()->with('success', 'Berhasil mereimburse nominal minus beserta catatan Anda.');
    }
}
