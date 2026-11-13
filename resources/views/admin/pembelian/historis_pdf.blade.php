<!DOCTYPE html>
<html>
<head>
    <title>Historis Pembelian Supplier</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { margin: 0; }
        p { margin: 2px 0; }
    </style>
</head>
<body>
    @if(!isset($isExcel) || !$isExcel)
    <div class="header">
        <h2>Riwayat Historis Pembelian (PO) ke Supplier</h2>
        @if($startDate && $endDate)
            <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</p>
        @else
            <p>Periode: Semua Waktu</p>
        @endif
        <hr>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>No. Referensi PO</th>
                <th>Tanggal Pembelian</th>
                <th>Supplier</th>
                <th>Kontak (Email/No.HP)</th>
                <th>Admin Pembuat</th>
                <th>Metode Bayar</th>
                <th>Status Pembelian</th>
                <th>Item Barang Fisik (Qty)</th>
                <th>Total Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($pembelians as $beli)
                @php $grandTotal += $beli->total_harga; @endphp
                <tr>
                    <td>PO-{{ str_pad($beli->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ \Carbon\Carbon::parse($beli->tanggal_pembelian)->translatedFormat('d M Y') }}</td>
                    <td>{{ $beli->supplier->nama_supplier ?? 'Umum' }}</td>
                    <td>{{ $beli->supplier->kontak ?? '-' }}</td>
                    <td>{{ $beli->admin->name ?? 'Sistem' }}</td>
                    <td>{{ str_replace('_', ' ', strtoupper($beli->metode_pembayaran ?? '-')) }}</td>
                    <td>{{ strtoupper($beli->status) }}</td>
                    <td>
                        <ul style="margin: 0; padding-left: 15px;">
                            @foreach($beli->detailPembelian as $detail)
                                <li>{{ $detail->barangFisik->nama_barang ?? 'Unknown' }} ({{ $detail->qty }} unit)</li>
                            @endforeach
                        </ul>
                    </td>
                    <td style="text-align: right;">{{ number_format($beli->total_harga, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" style="text-align: right;">Grand Total:</th>
                <th style="text-align: right;">{{ number_format($grandTotal, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
