<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Riwayat Transaksi Kasir</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { margin: 0 0 10px 0; font-size: 24px; color: #2c3e50; }
        .header p { margin: 0; font-size: 14px; color: #555; }
        .filters { margin-bottom: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; }
        .filters strong { display: inline-block; width: 120px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #2c3e50; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .badge-qris { background: #3498db; color: white; }
        .badge-cash { background: #2ecc71; color: white; }
        .badge-admin { background: #f1c40f; color: white; }
        @media print {
            body { padding: 0; }
            .btn-print { display: none; }
        }
        .btn-print { margin-bottom: 20px; padding: 10px 20px; background: #2980b9; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 4px; }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

    <div class="header">
        <h1>Laporan Riwayat Transaksi Kasir</h1>
        <p>Rekapitulasi Penjualan & Layanan Cabang</p>
    </div>

    <div class="filters">
        <div><strong>Cabang:</strong> {{ $cabang ? $cabang->nama_cabang : 'Semua Cabang' }}</div>
        <div>
            <strong>Periode:</strong> 
            {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Awal' }} 
            s/d 
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Sekarang' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu Transaksi</th>
                <th>Cabang</th>
                <th>Kasir</th>
                <th>Metode</th>
                <th>Diskon</th>
                <th>Total Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalSemuaTagihan = 0;
                $totalSemuaDiskon = 0;
            @endphp
            @forelse($transaksis as $index => $trx)
                @php 
                    $totalSemuaTagihan += $trx->total_tagihan;
                    $totalSemuaDiskon += $trx->total_diskon;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($trx->tanggal_transaksi)->translatedFormat('d M Y H:i') }}</td>
                    <td>
                        {{ $trx->cabang->nama_cabang ?? 'Pusat' }}
                    </td>
                    <td>
                        {{ $trx->kasir->name ?? 'Sistem' }}
                        @if($trx->is_bantuan_pusat)
                            <br><span class="badge badge-admin">Via Admin Pusat</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $trx->metode_pembayaran === 'qris' ? 'badge-qris' : 'badge-cash' }}">
                            {{ strtoupper(str_replace('_', ' ', $trx->metode_pembayaran)) }}
                        </span>
                    </td>
                    <td class="text-right text-red-500">
                        @if($trx->total_diskon > 0)
                            Rp {{ number_format($trx->total_diskon, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right font-bold">
                        Rp {{ number_format($trx->total_tagihan, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($transaksis) > 0)
        <tfoot>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL KESELURUHAN</td>
                <td class="text-right" style="color: #e74c3c;">Rp {{ number_format($totalSemuaDiskon, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalSemuaTagihan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

</body>
</html>
