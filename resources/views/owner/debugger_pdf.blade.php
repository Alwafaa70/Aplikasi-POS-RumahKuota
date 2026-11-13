<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kalkulasi SES Debugger</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 15px; }
        .header h2 { margin: 0 0 5px 0; font-size: 20px; color: #2c3e50; text-transform: uppercase; }
        .header p { margin: 0; color: #7f8c8d; font-size: 11px; }
        .filter-info { margin-bottom: 20px; font-size: 11px; border: 1px solid #ecf0f1; padding: 10px; background-color: #f9fbfc; border-radius: 4px; }
        .filter-info strong { color: #2c3e50; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .table th, .table td { border: 1px solid #bdc3c7; padding: 6px; text-align: left; vertical-align: middle; }
        .table th { background-color: #34495e; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 10px; text-align: center; }
        .table tr:nth-child(even) { background-color: #f8f9fa; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .highlight-row { background-color: #e8f4f8 !important; font-weight: bold; border: 2px solid #3498db; }
        .summary-box { background: #e8f4f8; border: 1px solid #bce8f1; padding: 15px; border-radius: 4px; text-align: center; margin-bottom: 20px; }
        .summary-title { font-size: 14px; font-weight: bold; color: #31708f; margin-bottom: 5px; }
        .summary-value { font-size: 24px; font-weight: bold; color: #2980b9; }
    </style>
</head>
<body>
    <div class="header">
        <h2>HASIL KALKULASI DINAMIS (SES DEBUGGER)</h2>
        <p>Sistem Cerdas Manajemen Inventaris - Dicetak pada: {{ date('d F Y H:i:s') }}</p>
    </div>

    <div class="filter-info">
        <strong>Parameter Kalkulasi:</strong><br>
        <table style="width: 100%; border:none; margin-top: 5px;">
            <tr>
                <td style="border:none; padding: 2px; width:120px;">Lokasi Cabang</td>
                <td style="border:none; padding: 2px;">: <strong>{{ $cabangName }}</strong></td>
                <td style="border:none; padding: 2px; width:120px;">Range Agregasi</td>
                <td style="border:none; padding: 2px;">: <strong>Per {{ $rangeDays }} Hari</strong></td>
            </tr>
            <tr>
                <td style="border:none; padding: 2px;">Nama Barang</td>
                <td style="border:none; padding: 2px;">: <strong>{{ $barangName }}</strong></td>
                <td style="border:none; padding: 2px;">Alpha Terbaik (Optimal)</td>
                <td style="border:none; padding: 2px;">: <strong>{{ $hasil['best_alpha'] }}</strong></td>
            </tr>
            <tr>
                <td style="border:none; padding: 2px;">Batas Kritis EWS</td>
                <td style="border:none; padding: 2px;">: <strong>{{ $batasEws ?? '-' }} Pcs</strong> (Lead Time: {{ $leadTime ?? 7 }} Hari)</td>
                <td style="border:none; padding: 2px;">Buffer Safety Stock</td>
                <td style="border:none; padding: 2px;">: <strong>{{ $safetyStockPercent }}%</strong> (Otomatis: {{ $klasifikasiFsn ?? 'FSN Analysis' }})</td>
            </tr>
            <tr>
                <td style="border:none; padding: 2px;">Sisa Stok Fisik</td>
                <td style="border:none; padding: 2px;">: <strong>{{ number_format($jumlahStok, 0, ',', '.') }} Pcs</strong></td>
                <td style="border:none; padding: 2px;">Status EWS</td>
                <td style="border:none; padding: 2px;">: <strong style="color: {{ !empty($isKritis) ? '#c0392b' : '#27ae60' }};">{{ !empty($isKritis) ? 'KRITIS (Menipis)' : 'AMAN' }}</strong></td>
            </tr>
        </table>
    </div>

    @php
        $mapeVal = $hasil['terkecil_mape'];
        if ($mapeVal < 10) {
            $kategoriLewis = 'Sangat Akurat (MAPE < 10%)';
            $badgeColor = '#27ae60';
        } elseif ($mapeVal <= 20) {
            $kategoriLewis = 'Baik / Akurat (10% ≤ MAPE ≤ 20%)';
            $badgeColor = '#2980b9';
        } elseif ($mapeVal <= 50) {
            $kategoriLewis = 'Layak / Cukup (20% < MAPE ≤ 50%)';
            $badgeColor = '#f39c12';
        } else {
            $kategoriLewis = 'Kurang Akurat (MAPE > 50%)';
            $badgeColor = '#c0392b';
        }
    @endphp

    <div class="summary-box">
        <div class="summary-title">Rekomendasi Restok (Periode Berikutnya)</div>
        <div class="summary-value">{{ number_format($rekomendasiRestok, 0, ',', '.') }} Pcs</div>
        <div style="font-size: 11px; color: #34495e; margin-top: 8px;">
            <strong>Rincian Kalkulasi:</strong><br>
            [Prediksi SES: {{ number_format($hasil['nilai_peramalan'], 0, ',', '.') }}] 
            + [Safety Stock ({{ $safetyStockPercent }}%): {{ number_format($safetyStock, 0, ',', '.') }}] 
            - [Stok Saat Ini: {{ number_format($jumlahStok, 0, ',', '.') }}] 
            = {{ number_format($rekomendasiRestok, 0, ',', '.') }}
        </div>
        <div style="font-size: 11px; margin-top: 6px;">
            Tingkat Akurasi (C.D. Lewis 1982): <strong style="color: {{ $badgeColor }};">{{ $kategoriLewis }}</strong> 
            (Alpha optimal {{ $hasil['best_alpha'] }} dengan MAPE terkecil: {{ $hasil['terkecil_mape'] }}%)
        </div>
    </div>

    <h3 style="font-size: 13px; color: #2c3e50; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Tabel Evaluasi Grid Search Alpha (0.1 - 0.9)</h3>
    <table class="table" style="width: 90%; margin: 0 auto;">
        <thead>
            <tr>
                <th style="width: 15%">Alpha (α)</th>
                <th style="width: 25%">Mean Absolute Deviation (MAD)</th>
                <th style="width: 20%">MAPE (%)</th>
                <th style="width: 25%">Kategori (Lewis 1982)</th>
                <th style="width: 15%">Prediksi (Pcs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hasil['history_mape'] as $history)
            @php
                $itemMape = $history['mape'];
                $kat = $itemMape < 10 ? 'Sangat Baik' : ($itemMape <= 20 ? 'Baik' : ($itemMape <= 50 ? 'Layak' : 'Rendah'));
            @endphp
            <tr class="{{ $history['alpha'] == $hasil['best_alpha'] ? 'highlight-row' : '' }}">
                <td class="text-center">{{ $history['alpha'] }} {{ $history['alpha'] == $hasil['best_alpha'] ? '★' : '' }}</td>
                <td class="text-center">{{ number_format($history['mad'], 2) }}</td>
                <td class="text-center">{{ number_format($history['mape'], 2) }}%</td>
                <td class="text-center font-bold">{{ $kat }}</td>
                <td class="text-center font-bold">{{ number_format($history['forecast_next'], 0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="text-align: center; font-size: 10px; color: #7f8c8d; margin-bottom: 20px;">
        * Baris yang disorot (★) adalah nilai Alpha optimal yang dipilih sistem berdasarkan nilai deviasi MAD minimum.<br>
        * Rujukan Klasifikasi Akurasi: Lewis, C. D. (1982). Industrial and business forecasting methods. Butterworth-Heinemann.
    </div>

    <h3 style="font-size: 13px; color: #2c3e50; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Data Aktual vs Fitted (Berdasarkan Agregasi)</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Periode Agregasi ({{ $rangeDays }} Hari)</th>
                <th>Data Aktual (Pcs)</th>
                <th>Hasil Fitting / Ramalan (Pcs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hasil['chart_labels'] as $index => $label)
                @if(isset($hasil['chart_aktual'][$index]) || isset($hasil['chart_forecast_fitted'][$index]))
                <tr>
                    <td class="text-center">{{ $label }}</td>
                    <td class="text-center">{{ $hasil['chart_aktual'][$index] !== null ? number_format($hasil['chart_aktual'][$index], 0) : '-' }}</td>
                    <td class="text-center">{{ $hasil['chart_forecast_fitted'][$index] !== null ? number_format($hasil['chart_forecast_fitted'][$index], 2) : '-' }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; font-size: 10px; color: #7f8c8d;">
        <p>Laporan ini dicetak dari halaman Simulator SES (Debugger).</p>
        <p>Tingkat keakuratan bergantung pada jumlah data historis dan rentang agregasi yang dipilih.</p>
    </div>
</body>
</html>
