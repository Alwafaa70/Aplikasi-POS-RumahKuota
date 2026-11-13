<!DOCTYPE html>
<html>
<head>
    <title>Laporan Aset & Stok Barang</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        h2 { text-align: center; margin-bottom: 5px; }
        h3 { margin-bottom: 5px; margin-top: 15px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .summary-box { border: 1px solid #000; padding: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>Laporan Valuasi Aset & Stok Barang</h2>
    <p class="text-center">Dicetak pada: {{ date('d/m/Y H:i') }}</p>

    <div class="summary-box">
        <strong>Ringkasan Total Aset:</strong>
        <ul>
            <li>Total Nilai Aset Seluruhnya: Rp {{ number_format($semuaAset->sum('nilai_aset'), 0, ',', '.') }}</li>
            <li>Nilai Aset di Gudang Pusat: Rp {{ number_format($semuaAset->where('lokasi', 'Gudang Pusat')->sum('nilai_aset'), 0, ',', '.') }}</li>
            <li>Nilai Aset Tersebar di Cabang: Rp {{ number_format($semuaAset->where('lokasi', '!=', 'Gudang Pusat')->sum('nilai_aset'), 0, ',', '.') }}</li>
        </ul>
    </div>

    <h3>Rincian Stok Barang</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Lokasi</th>
                <th>Nama Barang</th>
                <th>Batch / Tgl Masuk</th>
                <th>Usia (Hari)</th>
                <th>Sisa Stok</th>
                <th>Harga Modal (HPP)</th>
                <th>Total Nilai Aset</th>
            </tr>
        </thead>
        <tbody>
            @forelse($semuaAset as $index => $aset)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $aset->lokasi }}</td>
                <td class="font-bold">{{ $aset->nama_barang }}</td>
                <td>{{ $aset->batch_id }}<br>{{ date('d/m/Y', strtotime($aset->tanggal_masuk)) }}</td>
                <td class="text-center">{{ $aset->usia_hari }}</td>
                <td class="text-right">{{ number_format($aset->stok, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($aset->hpp, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp {{ number_format($aset->nilai_aset, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Belum ada data stok/aset.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
