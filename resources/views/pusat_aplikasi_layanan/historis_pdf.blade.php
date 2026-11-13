<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Besar (Ledger) Dompet & Kas Pusat</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 5px 0 0; color: #666; }
        .summary-box { border: 1px solid #ddd; padding: 10px; margin-bottom: 20px; background: #f9f9f9; }
        .summary-box h3 { margin-top: 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-green { color: green; font-weight: bold; }
        .badge-blue { color: #2563eb; font-weight: bold; }
        .badge-red { color: red; font-weight: bold; }
        .badge-gray { color: gray; }
    </style>
</head>
<body>

    <div class="header">
        <h1>BUKU BESAR (LEDGER) DOMPET & KAS PUSAT</h1>
        <p>
            Periode: 
            {{ $startDate ? \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') : 'Awal' }} 
            s/d 
            {{ $endDate ? \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') : 'Sekarang' }}
        </p>
    </div>

    <div class="summary-box">
        <h3 style="margin-bottom: 8px;">Ringkasan Mutasi per Layanan & Kas Pusat</h3>
        <table style="margin-bottom: 0;">
            <thead>
                <tr>
                    <th>Nama Layanan / Akun</th>
                    <th class="text-right">Saldo Awal</th>
                    <th class="text-right">Masuk (+)</th>
                    <th class="text-right">Keluar (-)</th>
                    <th class="text-right">Saldo Akhir Periode</th>
                    <th class="text-right">Saldo Sekarang</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($dompetSummaries))
                    @foreach($dompetSummaries as $d)
                    <tr>
                        <td><strong>Digital ({{ $d->nama_dompet }})</strong></td>
                        <td class="text-right">Rp {{ number_format($d->saldo_awal, 0, ',', '.') }}</td>
                        <td class="text-right badge-green">+ {{ number_format($d->masuk, 0, ',', '.') }}</td>
                        <td class="text-right badge-red">- {{ number_format($d->keluar, 0, ',', '.') }}</td>
                        <td class="text-right"><strong>Rp {{ number_format($d->saldo_akhir, 0, ',', '.') }}</strong></td>
                        <td class="text-right">Rp {{ number_format($d->saldo_sekarang, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @endif
                @if(isset($kasSummary))
                <tr style="background-color: #f2fdf4;">
                    <td><strong>Kas Fisik (Pusat)</strong></td>
                    <td class="text-right">Rp {{ number_format($kasSummary->saldo_awal, 0, ',', '.') }}</td>
                    <td class="text-right badge-green">+ {{ number_format($kasSummary->masuk, 0, ',', '.') }}</td>
                    <td class="text-right badge-red">- {{ number_format($kasSummary->keluar, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($kasSummary->saldo_akhir, 0, ',', '.') }}</strong></td>
                    <td class="text-right">Rp {{ number_format($kasSummary->saldo_sekarang, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <h3>1. Riwayat Uang & Saldo Masuk (Inflow)</h3>
    @if($inflows->count() > 0)
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal / Waktu</th>
                <th>Tipe Uang</th>
                <th class="text-right">Nominal (Rp)</th>
                <th>Keterangan / Sumber</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inflows as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y H:i') }}</td>
                <td>
                    @if($item->tipe_uang === 'Digital')
                        <span class="badge-blue">Digital ({{ $item->nama_dompet }})</span>
                    @else
                        <span class="badge-green">Fisik (Kas)</span>
                    @endif
                </td>
                <td class="text-right badge-green">+ {{ number_format($item->nominal_custom, 0, ',', '.') }}</td>
                <td>{!! $item->keterangan_custom !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="badge-gray"><em>Tidak ada riwayat uang/saldo masuk pada periode ini.</em></p>
    @endif

    <br>

    <h3>2. Riwayat Pengeluaran & Bantuan Keluar (Outflow)</h3>
    @if($outflows->count() > 0)
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal / Waktu</th>
                <th>Tipe Uang</th>
                <th class="text-right">Nominal (Rp)</th>
                <th>Keterangan / Tujuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($outflows as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y H:i') }}</td>
                <td>
                    @if($item->tipe_uang === 'Digital')
                        <span class="badge-blue">Digital ({{ $item->nama_dompet }})</span>
                    @else
                        <span class="badge-green">Fisik (Kas)</span>
                    @endif
                </td>
                <td class="text-right badge-red">- {{ number_format($item->nominal_custom, 0, ',', '.') }}</td>
                <td>{!! $item->keterangan_custom !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="badge-gray"><em>Tidak ada riwayat pengeluaran pada periode ini.</em></p>
    @endif

</body>
</html>
