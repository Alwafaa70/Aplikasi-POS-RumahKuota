<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Riwayat Pengiriman Mutasi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f4f4f4; text-align: center; font-weight: bold; }
        .status-badge { padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 8px 15px; font-size: 14px; cursor: pointer; background: #3498db; color: white; border: none; border-radius: 4px;">🖨️ Cetak Laporan Sekarang</button>
    </div>

    <div class="header">
        <h2>Laporan Riwayat Pengiriman Mutasi Stok</h2>
        <p>Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Waktu Mutasi</th>
                <th style="width: 20%;">Asal ➔ Tujuan</th>
                <th style="width: 30%;">Rincian Barang</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 15%;">Dikirim Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mutasiStok as $index => $rm)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($rm->created_at)->format('d M Y, H:i') }}</td>
                    <td>
                        <strong>{{ $rm->cabangAsal ? $rm->cabangAsal->nama_cabang : 'Gudang Pusat' }}</strong><br>
                        ➔ {{ $rm->cabangTujuan ? $rm->cabangTujuan->nama_cabang : 'Unknown' }}
                    </td>
                    <td>
                        <ul style="margin: 0; padding-left: 15px;">
                        @foreach($rm->detailMutasi as $dtl)
                            <li>
                                {{ $dtl->barang->nama_barang ?? 'Unknown' }} ({{ $dtl->kuantitas }})
                                @if($rm->status == 'diterima' && ($dtl->qty_rusak > 0 || $dtl->qty_hilang > 0))
                                    <br><small style="color: red;">Rusak: {{ $dtl->qty_rusak }}, Hilang: {{ $dtl->qty_hilang }}</small>
                                @endif
                            </li>
                        @endforeach
                        </ul>
                    </td>
                    <td style="text-align: center;">
                        @if($rm->status == 'dikirim')
                            <span class="status-badge" style="background: #f39c12; color: #fff;">In Transit</span>
                        @else
                            @if($rm->status_penerimaan == 'bermasalah')
                                <span class="status-badge" style="background: #e74c3c; color: #fff;">Bermasalah</span>
                            @else
                                <span class="status-badge" style="background: #27ae60; color: #fff;">Selesai</span>
                            @endif
                        @endif
                    </td>
                    <td>{{ $rm->admin->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data mutasi stok pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d M Y, H:i') }}</p>
    </div>
</body>
</html>
