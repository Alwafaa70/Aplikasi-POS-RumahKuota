<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk QRIS - Transaksi #{{ $transaksi->id }}</title>
    <style>
        body {
            font-family: monospace;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .receipt {
            background-color: white;
            width: 300px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-dashed { border-bottom: 1px dashed #000; margin: 10px 0; }
        .mb-2 { margin-bottom: 10px; }
        .item-row { display: flex; justify-content: space-between; }
        .print-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #3b82f6;
            color: white;
            text-align: center;
            text-decoration: none;
            margin-top: 20px;
            border-radius: 4px;
        }
        @media print {
            .print-btn { display: none; }
            body { background: white; }
            .receipt { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="text-center mb-2">
            <h2 style="margin:0;">TOKO KITA</h2>
            <p style="margin:5px 0;">Cabang: {{ $transaksi->cabang->nama_cabang }}</p>
            <p style="margin:0; font-size: 12px;">Kasir: {{ $transaksi->kasir->name }}</p>
        </div>

        <div class="border-dashed"></div>

        <div class="mb-2" style="font-size: 12px;">
            <div>No  : TRX-{{ str_pad($transaksi->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div>Tgl : {{ $transaksi->tanggal_transaksi }}</div>
            <div>Metode: <span class="font-bold">QRIS</span></div>
        </div>

        <div class="border-dashed"></div>

        <div class="mb-2">
            @foreach($transaksi->detailTransaksi as $detail)
            <div style="margin-bottom: 5px;">
                <div>{{ $detail->barang ? $detail->barang->nama_barang : 'Produk Digital A' }}</div>
                <div class="item-row">
                    <span>{{ $detail->kuantitas }} x {{ number_format($detail->harga_jual_final, 0, ',', '.') }}</span>
                    <span>{{ number_format($detail->kuantitas * $detail->harga_jual_final, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="border-dashed"></div>

        <div class="item-row font-bold" style="font-size: 16px;">
            <span>TOTAL</span>
            <span>Rp {{ number_format($transaksi->total_tagihan, 0, ',', '.') }}</span>
        </div>

        <div class="border-dashed"></div>

        <div class="text-center" style="font-size: 12px; margin-top: 20px;">
            <p>Terima Kasih</p>
            <p>Layanan Pembayaran Digital Terverifikasi</p>
        </div>

        <a href="#" class="print-btn" onclick="window.print()">Cetak Struk</a>
        <a href="{{ route('kasir.dashboard') }}" class="print-btn" style="background: #6b7280; margin-top: 10px;">Kembali</a>
    </div>
</body>
</html>
