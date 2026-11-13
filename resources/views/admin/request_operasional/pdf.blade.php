<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Request Operasional Cabang</title>
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
        .badge { display: inline-block; padding: 3px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .badge-pending { background: #f39c12; color: white; }
        .badge-waiting { background: #3498db; color: white; }
        .badge-completed { background: #2ecc71; color: white; }
        .badge-rejected { background: #e74c3c; color: white; }
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
        <h1>Laporan Request Operasional Cabang</h1>
        <p>Rekapitulasi Permintaan Isi Saldo, Restok Fisik, dan Bantuan Transaksi</p>
    </div>

    <div class="filters">
        <div><strong>Cabang:</strong> {{ $cabang ? $cabang->nama_cabang : 'Semua Cabang' }}</div>
        <div>
            <strong>Status Filter:</strong> 
            @if($status === 'pending') Menunggu Diproses Admin
            @elseif($status === 'waiting_kasir') Menunggu Konfirmasi Kasir
            @elseif($status === 'completed') Selesai / Ditolak
            @else Semua Status
            @endif
        </div>
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
                <th>Tanggal</th>
                <th>Cabang</th>
                <th>Jenis Request</th>
                <th>Keterangan / Detail</th>
                <th>Nominal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allItems as $index => $item)
                @php
                    $isPending = ($item->normalized_status === 'menunggu_admin' || $item->normalized_status === 'menunggu_pusat');
                    $isWaitingKasir = ($item->normalized_status === 'menunggu_konfirmasi_kasir' || $item->normalized_status === 'diproses_admin');
                    $isCompleted = ($item->normalized_status === 'selesai');
                    $isRejected = ($item->normalized_status === 'ditolak');

                    if ($isPending) $badgeClass = 'badge-pending';
                    elseif ($isWaitingKasir) $badgeClass = 'badge-waiting';
                    elseif ($isCompleted) $badgeClass = 'badge-completed';
                    else $badgeClass = 'badge-rejected';

                    $statusText = $isPending ? 'Pending' : ($isWaitingKasir ? 'Waiting Kasir' : ($isCompleted ? 'Selesai' : 'Ditolak'));
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->cabang->nama_cabang }}</td>
                    <td>
                        @if(!$item->is_bantuan)
                            {{ strtoupper(str_replace('_', ' ', $item->jenis_request)) }}
                        @else
                            BANTUAN TRANSAKSI (DIGITAL B)
                        @endif
                    </td>
                    <td>
                        @if(!$item->is_bantuan)
                            @if(in_array($item->jenis_request, ['restok_barang', 'barang_fisik']))
                                <ul>
                                    @foreach($item->detail as $dtl)
                                        <li>{{ $dtl->barangFisik->nama_barang ?? 'Unknown' }} ({{ $dtl->nominal_atau_qty }})</li>
                                    @endforeach
                                </ul>
                            @else
                                {{ $item->masterAplikasiLayanan->nama_layanan ?? '-' }}
                            @endif
                            
                            @if($item->keterangan) <br><i>Catatan: {{ $item->keterangan }}</i> @endif
                        @else
                            Tujuan: {{ $item->barang->nama_barang ?? '-' }}<br>
                            No Rek/Tujuan: {{ $item->nomor_tujuan }}
                            @if($item->keterangan) <br><i>Catatan: {{ $item->keterangan }}</i> @endif
                        @endif
                    </td>
                    <td>
                        @if(!$item->is_bantuan)
                            @if(!in_array($item->jenis_request, ['restok_barang', 'barang_fisik']))
                                Rp {{ number_format($item->nominal_request, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        @else
                            Rp {{ number_format($item->amount, 0, ',', '.') }}
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data request pada rentang waktu atau filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
