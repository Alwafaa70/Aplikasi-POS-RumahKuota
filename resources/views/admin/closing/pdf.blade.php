<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Closing</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Riwayat Closing Cabang</h2>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
        @if(request('start_date') && request('end_date'))
            <p>Periode: {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}</p>
        @endif
        @if(request('cabang_id') && request('cabang_id') != 'all')
            <p>Cabang: {{ \App\Models\Cabang::find(request('cabang_id'))->nama_cabang ?? 'Unknown' }}</p>
        @else
            <p>Cabang: Semua Cabang</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Cabang</th>
                <th>Kasir</th>
                <th>Rincian Saldo Aktual</th>
                <th>Status Selisih (Plus/Minus)</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($closings as $closing)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($closing->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $closing->cabang->nama_cabang }}</td>
                    <td>{{ $closing->kasir->name }}</td>
                    <td>
                        <div style="margin-bottom: 5px;">
                            <strong>Uang Laci:</strong> Rp {{ number_format($closing->uang_fisik_aktual, 0, ',', '.') }}
                        </div>
                        @foreach($closing->details as $apk)
                            <div style="margin-bottom: 2px;">
                                <strong>{{ $apk->masterAplikasiLayanan->nama_layanan ?? 'Aplikasi' }}:</strong> Rp {{ number_format($apk->saldo_aktual, 0, ',', '.') }}
                            </div>
                        @endforeach
                    </td>
                    <td>
                        <div style="margin-bottom: 5px;">
                            <strong>Uang Laci:</strong> 
                            @if($closing->status_fisik == 'balance')
                                <span style="color: green;">Balance</span>
                            @elseif($closing->status_fisik == 'plus')
                                <span style="color: blue;">+Rp {{ number_format(abs($closing->selisih_fisik), 0, ',', '.') }}</span>
                            @else
                                <span style="color: red;">-Rp {{ number_format(abs($closing->selisih_fisik), 0, ',', '.') }}</span>
                                @if(($closing->nominal_pengembalian ?? 0) > 0)
                                    <br><small style="color: {{ $closing->status_pengembalian_minus == 'dikembalikan' ? 'green' : 'orange' }};">
                                        ({{ $closing->status_pengembalian_minus == 'dikembalikan' ? 'Lunas Diganti' : 'Dicicil' }}: Rp {{ number_format($closing->nominal_pengembalian, 0, ',', '.') }})
                                    </small>
                                @endif
                            @endif
                        </div>
                        @foreach($closing->details as $apk)
                            <div style="margin-bottom: 2px;">
                                <strong>{{ $apk->masterAplikasiLayanan->nama_layanan ?? 'Aplikasi' }}:</strong> 
                                @if($apk->status == 'balance')
                                    <span style="color: green;">Balance</span>
                                @elseif($apk->status == 'plus')
                                    <span style="color: blue;">+Rp {{ number_format(abs($apk->selisih), 0, ',', '.') }}</span>
                                @else
                                    <span style="color: red;">-Rp {{ number_format(abs($apk->selisih), 0, ',', '.') }}</span>
                                    @if(($apk->nominal_pengembalian ?? 0) > 0)
                                        <br><small style="color: {{ $apk->status_pengembalian_minus == 'dikembalikan' ? 'green' : 'orange' }};">
                                            ({{ $apk->status_pengembalian_minus == 'dikembalikan' ? 'Lunas Diganti' : 'Dicicil' }}: Rp {{ number_format($apk->nominal_pengembalian, 0, ',', '.') }})
                                        </small>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @if($closing->catatan_kasir)
                            <strong>Kasir:</strong> {{ $closing->catatan_kasir }}<br>
                        @endif
                        @if($closing->catatan_admin)
                            <strong>Admin:</strong> {{ $closing->catatan_admin }}
                        @endif
                        @if(!$closing->catatan_kasir && !$closing->catatan_admin)
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
