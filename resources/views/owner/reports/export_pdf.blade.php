<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Eksekutif</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #2c3e50; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; color: #7f8c8d; }
        
        .section-title { font-size: 14px; color: #2c3e50; margin-top: 30px; margin-bottom: 10px; border-left: 4px solid #3498db; padding-left: 8px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #bdc3c7; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: bold; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .profit-table th { background-color: #2c3e50; color: white; }
        .profit-total { font-weight: bold; font-size: 14px; background-color: #ecf0f1; }
        .profit-final { font-weight: bold; font-size: 16px; background-color: #d4efdf; color: #27ae60; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; color: white; font-weight: bold; text-transform: uppercase; }
        .bg-green { background-color: #27ae60; }
        .bg-red { background-color: #e74c3c; }
        .bg-blue { background-color: #3498db; }
        .bg-yellow { background-color: #f39c12; }
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN EKSEKUTIF POS MULTI-CABANG</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <!-- 1. Laba Rugi -->
    <div class="section-title">1. LAPORAN LABA / RUGI</div>
    <table class="profit-table">
        <tr>
            <th>Komponen Keuangan</th>
            <th class="text-right">Nilai (Rp)</th>
        </tr>
        <tr>
            <td>Total Pendapatan Kotor (Omzet)</td>
            <td class="text-right">{{ number_format($profitability['total_pendapatan'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Beban HPP (Harga Pokok Penjualan)</td>
            <td class="text-right text-red">- {{ number_format($profitability['total_hpp'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Total Pendapatan Fee (Transaksi Digital)</td>
            <td class="text-right">+ {{ number_format($profitability['total_fee_digital_b'], 0, ',', '.') }}</td>
        </tr>
        <tr class="profit-final">
            <td>Laba Bersih Konsolidasi</td>
            <td class="text-right">{{ number_format($profitability['laba_kotor'], 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- 2. Distribusi Aset -->
    <div class="section-title">2. RIWAYAT DISTRIBUSI ASET (PUSAT KE CABANG)</div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tujuan</th>
                <th>Keterangan</th>
                <th class="text-right">Nilai HPP (Rp)</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($distribusi as $mutasi)
            <tr>
                <td>{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->format('d/m/Y') }}</td>
                <td>{{ $mutasi->cabangTujuan ? $mutasi->cabangTujuan->nama_cabang : '-' }}</td>
                <td>{{ $mutasi->keterangan }}</td>
                <td class="text-right">{{ number_format($mutasi->nilai_modal_dipindahkan ?? 0, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($mutasi->status == 'dikirim')
                        <span class="badge bg-yellow">Di Perjalanan</span>
                    @elseif($mutasi->status == 'selesai')
                        <span class="badge bg-green">Diterima</span>
                    @else
                        <span class="badge bg-blue">{{ $mutasi->status }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada riwayat distribusi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. Blind Closing Kasir -->
    <div class="section-title">3. DETAIL BLIND CLOSING KASIR (SELISIH SETORAN)</div>
    <table>
        <thead>
            <tr>
                <th>Waktu Closing</th>
                <th>Cabang & Kasir</th>
                <th class="text-right">Ekspektasi Sistem</th>
                <th class="text-right">Setoran Kasir</th>
                <th class="text-right">Selisih</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monitoringClosing as $closing)
            <tr>
                <td>{{ $closing->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <b>{{ $closing->cabang ? $closing->cabang->nama_cabang : '-' }}</b><br>
                    <span style="color: #7f8c8d;">{{ $closing->kasir ? $closing->kasir->name : '-' }}</span>
                </td>
                <td class="text-right">{{ number_format($closing->total_sistem, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($closing->total_aktual_input, 0, ',', '.') }}</td>
                <td class="text-right">
                    @if($closing->selisih > 0)
                        <span style="color: #27ae60; font-weight: bold;">+ {{ number_format($closing->selisih, 0, ',', '.') }}</span>
                    @elseif($closing->selisih < 0)
                        <span style="color: #e74c3c; font-weight: bold;">{{ number_format($closing->selisih, 0, ',', '.') }}</span>
                    @else
                        <span>0</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada aktivitas closing kasir pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 50px; text-align: right; color: #7f8c8d; font-size: 10px;">
        Dokumen ini digenerate secara otomatis oleh Sistem POS pada {{ now()->format('d M Y H:i') }}
    </div>

</body>
</html>
