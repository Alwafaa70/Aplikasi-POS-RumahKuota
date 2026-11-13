<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Master Barang</title>
    <style>
        body { font-family: 'Arial', sans-serif; margin: 20px; color: #333; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0 0 5px 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 0; color: #555; font-size: 14px; }
        .info { margin-bottom: 15px; font-size: 12px; }
        .info span { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background-color: #f4f4f4; text-transform: uppercase; font-size: 11px; color: #555; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { background: #e0e0e0; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        @media print {
            body { margin: 0; padding: 15px; }
            button { display: none; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>Laporan Data Master Barang</h1>
        <p>Aplikasi Kasir POS Terintegrasi</p>
    </div>

    <div class="info">
        <div style="float: left;">
            <p><span>Tipe Barang:</span> {{ $tipe ? $tipe : 'Semua Tipe' }}</p>
            <p><span>Kategori ID:</span> {{ $kategori ? $kategori : 'Semua' }}</p>
            @if($search)
            <p><span>Pencarian:</span> "{{ $search }}"</p>
            @endif
        </div>
        <div style="float: right; text-align: right;">
            <p><span>Tanggal Cetak:</span> {{ now()->format('d/m/Y H:i') }}</p>
            <p><span>Total Data:</span> {{ $barangs->count() }} item</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="10%">ID</th>
                <th width="15%">Tipe Barang</th>
                <th width="20%">Kategori (Induk ➔ Sub)</th>
                <th width="35%">Nama Produk / Barang</th>
                <th width="15%" class="text-right">Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barangs as $index => $barang)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>#{{ $barang->id }}</td>
                <td>{{ $barang->tipe_barang }}</td>
                <td>
                    @if($barang->category)
                        @if($barang->category->parent)
                            {{ $barang->category->parent->name }} ➔ {{ $barang->category->name }}
                        @else
                            {{ $barang->category->name }}
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td>
                    <strong>{{ $barang->nama_barang }}</strong>
                </td>
                <td class="text-right">
                    @if($barang->tipe_barang === 'Digital B')
                        <span class="badge">Range Fee</span>
                    @else
                        Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data yang sesuai dengan filter.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">Cetak Sekarang</button>
    </div>

</body>
</html>
