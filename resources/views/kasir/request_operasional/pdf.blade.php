<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Request Operasional - {{ Auth::user()->cabang->nama_cabang ?? 'Cabang' }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .header h2 { margin: 0 0 5px 0; color: #2c3e50; }
        .header p { margin: 0; color: #7f8c8d; font-size: 11px; }
        .filter-info { margin-bottom: 15px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #bdc3c7; padding: 8px; text-align: left; }
        th { background-color: #ecf0f1; font-weight: bold; color: #2c3e50; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status-badge { font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .footer { position: absolute; bottom: 30px; width: 100%; text-align: center; font-size: 10px; color: #95a5a6; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Request Bantuan Operasional</h2>
        <p>Cabang: {{ Auth::user()->cabang->nama_cabang ?? 'Tidak Diketahui' }}</p>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="filter-info">
        <b>Filter Diterapkan:</b><br>
        Tanggal: {{ $request->start_date ? \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') : 'Semua' }} s/d {{ $request->end_date ? \Carbon\Carbon::parse($request->end_date)->format('d/m/Y') : 'Semua' }}<br>
        Jenis Request: {{ $request->jenis_request ? ucwords(str_replace('_', ' ', $request->jenis_request)) : 'Semua Jenis' }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">ID</th>
                <th width="15%">Waktu</th>
                <th width="20%">Jenis Request</th>
                <th width="40%">Detail Permintaan</th>
                <th width="20%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                <tr>
                    <td>#{{ $req->id }}</td>
                    <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <strong style="text-transform: uppercase;">{{ str_replace('_', ' ', $req->jenis_request) }}</strong>
                        @if($req->tujuan_rekening)
                            <br><span style="color: #666;">Ke: {{ $req->tujuan_rekening }}</span>
                        @endif
                    </td>
                    <td>
                        @if($req->jenis_request === 'isi_saldo')
                            <strong>Top Up {{ $req->masterAplikasiLayanan ? $req->masterAplikasiLayanan->nama_layanan : 'E-Wallet' }}</strong><br>
                            Rp {{ number_format($req->nominal_request, 0, ',', '.') }}
                        @elseif($req->nominal_request)
                            Rp {{ number_format($req->nominal_request, 0, ',', '.') }}
                            @if($req->total_biaya_pelanggan)
                                <br><span style="color: #e74c3c;">Terima Cash Pelanggan: Rp {{ number_format($req->total_biaya_pelanggan, 0, ',', '.') }}</span>
                            @endif
                        @else
                            <ul style="margin: 0; padding-left: 15px;">
                            @foreach($req->detail as $dtl)
                                <li>{{ $dtl->barangFisik ? $dtl->barangFisik->nama_barang : 'Barang' }} ({{ $dtl->nominal_atau_qty }}x)</li>
                            @endforeach
                            </ul>
                        @endif
                        @if($req->keterangan)
                            <br><span style="font-size: 10px; color: #7f8c8d;">Ket: {{ $req->keterangan }}</span>
                        @endif
                    </td>
                    <td>
                        @if($req->status_request === 'menunggu_admin')
                            <span class="status-badge" style="color: #d35400;">Menunggu Pusat</span>
                        @elseif($req->status_request === 'menunggu_konfirmasi_kasir')
                            <span class="status-badge" style="color: #2980b9;">Dalam Pengiriman</span>
                        @elseif($req->status_request === 'selesai')
                            <span class="status-badge" style="color: #27ae60;">Selesai</span>
                        @else
                            <span class="status-badge">{{ $req->status_request }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Sistem POS Antigravity - {{ env('APP_NAME') }}
    </div>
</body>
</html>
