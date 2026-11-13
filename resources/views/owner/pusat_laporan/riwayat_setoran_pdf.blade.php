<!DOCTYPE html>
<html>
<head>
    <title>Laporan Riwayat Setoran Pusat</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Riwayat Setoran Pusat</h2>
    <p>
        <strong>Periode:</strong> {{ request('start_date') ? date('d/m/Y', strtotime(request('start_date'))) : 'Semua' }} s/d {{ request('end_date') ? date('d/m/Y', strtotime(request('end_date'))) : 'Semua' }}<br>
        <strong>Status:</strong> {{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Sumber Dompet</th>
                <th>Nominal</th>
                <th>Pengaju</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($setorans as $index => $setoran)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $setoran->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @if($setoran->pusat_aplikasi_layanan_id === null)
                        Kas Fisik Pusat
                    @else
                        {{ $setoran->dompet->masterAplikasiLayanan->nama_layanan ?? 'Digital' }}
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($setoran->nominal, 0, ',', '.') }}</td>
                <td>{{ $setoran->jenis_pengaju === 'owner' ? 'Owner' : 'Admin' }}</td>
                <td>
                    @if($setoran->status === 'pending_admin') Pending Admin
                    @elseif($setoran->status === 'pending_owner') Pending Owner
                    @elseif($setoran->status === 'selesai') Selesai
                    @elseif($setoran->status === 'ditolak') Ditolak
                    @else Batal @endif
                </td>
                <td>
                    P: {{ $setoran->catatan_pengaju ?? '-' }}<br>
                    T: {{ $setoran->catatan_konfirmasi ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data setoran.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
