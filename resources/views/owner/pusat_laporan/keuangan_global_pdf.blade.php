<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan Global Pusat</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; text-align: center; }
        h2, h3 { text-align: center; margin-bottom: 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .summary-box { border: 1px solid #000; padding: 10px; margin-bottom: 20px; background-color: #fafafa; }
        .mutasi-masuk { color: green; font-weight: bold; }
        .mutasi-keluar { color: red; font-weight: bold; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <h2>Laporan Keuangan Global Pusat</h2>
    <p class="text-center">Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}</p>
    <p class="text-center" style="margin-bottom:20px;">Dicetak pada: {{ date('d/m/Y H:i') }}</p>

    <!-- Ringkasan Global -->
    <div class="summary-box">
        <h3 style="margin-top:0;">NERACA GLOBAL (KAS FISIK + DOMPET DIGITAL)</h3>
        <table style="margin-top:0; border:none;">
            <tr>
                <td style="border:none; width:25%;"><strong>Total Saldo Awal:</strong><br>Rp {{ number_format($kasFisikAwal + $totalDompetAwal, 0, ',', '.') }}</td>
                <td style="border:none; width:25%; color:green;"><strong>Total Pemasukan:</strong><br>+ Rp {{ number_format($pemasukanFisik + $totalDompetPemasukan, 0, ',', '.') }}</td>
                <td style="border:none; width:25%; color:red;"><strong>Total Pengeluaran:</strong><br>- Rp {{ number_format($pengeluaranFisik + $totalDompetPengeluaran, 0, ',', '.') }}</td>
                <td style="border:none; width:25%;"><strong>Total Saldo Akhir:</strong><br>Rp {{ number_format($kasFisikAkhir + $totalDompetAkhir, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Rincian Kas Fisik -->
    <h3 style="text-align:left; border-bottom:1px solid #000;">1. RINCIAN ARUS KAS FISIK (TUNAI)</h3>
    <p>
        Saldo Awal (seb. {{ date('d/m/Y', strtotime($startDate)) }}): <strong>Rp {{ number_format($kasFisikAwal, 0, ',', '.') }}</strong><br>
        Saldo Akhir ({{ date('d/m/Y', strtotime($endDate)) }}): <strong>Rp {{ number_format($kasFisikAkhir, 0, ',', '.') }}</strong>
    </p>
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Keterangan Transaksi</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Saldo Berjalan</th>
            </tr>
        </thead>
        <tbody>
            @if(count($mutasiFisik) == 0)
            <tr><td colspan="5" class="text-center">Tidak ada pergerakan kas fisik di periode ini.</td></tr>
            @else
                @foreach($mutasiFisik as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->keterangan }}</td>
                    <td class="text-right mutasi-masuk">{{ $m->jenis_mutasi == 'masuk' ? '+ '.number_format($m->nominal, 0, ',', '.') : '' }}</td>
                    <td class="text-right mutasi-keluar">{{ $m->jenis_mutasi == 'keluar' ? '- '.number_format($m->nominal, 0, ',', '.') : '' }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($m->saldo_akhir, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- Rincian Dompet Digital -->
    <h3 style="text-align:left; border-bottom:1px solid #000;">2. RINCIAN ARUS SALDO DOMPET DIGITAL</h3>
    
    @foreach($rekapDompet as $index => $dompet)
    <div style="margin-bottom: 20px;">
        <p>
            <strong>2.{{ $index + 1 }}. {{ $dompet->nama }} ({{ $dompet->rekening }})</strong><br>
            Saldo Awal: Rp {{ number_format($dompet->awal, 0, ',', '.') }} &nbsp;|&nbsp; 
            Pemasukan: <span class="mutasi-masuk">+Rp {{ number_format($dompet->masuk, 0, ',', '.') }}</span> &nbsp;|&nbsp; 
            Pengeluaran: <span class="mutasi-keluar">-Rp {{ number_format($dompet->keluar, 0, ',', '.') }}</span> &nbsp;|&nbsp; 
            <strong>Saldo Akhir: Rp {{ number_format($dompet->akhir, 0, ',', '.') }}</strong>
        </p>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Keterangan Transaksi</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Saldo Berjalan</th>
                </tr>
            </thead>
            <tbody>
                @if(count($dompet->mutasi) == 0)
                <tr><td colspan="5" class="text-center">Tidak ada pergerakan saldo di periode ini.</td></tr>
                @else
                    @foreach($dompet->mutasi as $m)
                    <tr>
                        <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $m->keterangan }}</td>
                        <td class="text-right mutasi-masuk">{{ $m->jenis_mutasi == 'masuk' ? '+ '.number_format($m->nominal, 0, ',', '.') : '' }}</td>
                        <td class="text-right mutasi-keluar">{{ $m->jenis_mutasi == 'keluar' ? '- '.number_format($m->nominal, 0, ',', '.') : '' }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($m->saldo_akhir, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    @endforeach

    <br>
    <table style="width:100%; border:none; margin-top:50px;">
        <tr>
            <td style="border:none; text-align:right; width:50%;"></td>
            <td style="border:none; text-align:center; width:50%;">
                Mengetahui,<br><br><br><br><br>
                <strong>( Owner )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
