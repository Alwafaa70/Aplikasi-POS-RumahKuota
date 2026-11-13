<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Omzet</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; }
        .filter-info { margin-bottom: 15px; font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table th, .table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .table th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; background-color: #e0e0e0; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN OMZET KOTOR TRANSAKSI</h2>
        <p style="margin: 5px 0 0 0;">Dicetak pada: {{ date('d M Y H:i:s') }}</p>
    </div>

    <div class="filter-info">
        <strong>Filter Diterapkan:</strong><br>
        Tanggal Mulai: {{ $filters['start_date'] ?? 'Semua' }}<br>
        Tanggal Akhir: {{ $filters['end_date'] ?? 'Semua' }}<br>
        Cabang ID: {{ $filters['cabang_id'] ?? 'Semua Cabang' }}
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID Trans.</th>
                <th>Tanggal</th>
                <th>Cabang</th>
                <th>Kasir</th>
                <th>Metode</th>
                <th>Tagihan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksis as $trx)
            <tr>
                <td>#{{ $trx->id }}</td>
                <td>{{ $trx->tanggal_transaksi }}</td>
                <td>{{ $trx->cabang ? $trx->cabang->nama_cabang : '-' }}</td>
                <td>{{ $trx->kasir ? $trx->kasir->name : '-' }}</td>
                <td>{{ strtoupper($trx->metode_pembayaran) }}</td>
                <td class="text-right">{{ number_format($trx->total_tagihan, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL OMZET KOTOR:</td>
                <td class="text-right">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
