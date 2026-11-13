<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Buffer EWS</title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2.5px solid #1e293b;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: 900;
            margin: 0 0 3px 0;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 10px;
            color: #64748b;
            margin: 0;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 2px 4px;
            font-size: 9.5px;
            vertical-align: top;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .summary-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 14px;
        }
        .summary-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }
        .summary-card.kritis {
            background: #fef2f2;
            border-color: #fca5a5;
        }
        .summary-card.aman {
            background: #f0fdf4;
            border-color: #86efac;
        }
        .summary-card.restok {
            background: #eef2ff;
            border-color: #a5b4fc;
        }
        .summary-title {
            font-size: 8.5px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-val {
            font-size: 18px;
            font-weight: 900;
            margin-top: 2px;
            color: #0f172a;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .table th, .table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            font-size: 9px;
            vertical-align: middle;
        }
        .table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5px;
            text-align: center;
        }
        .table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .table tr.row-kritis {
            background-color: #fee2e2 !important;
            font-weight: 600;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
        }
        .badge-kritis {
            background: #ef4444;
            color: #ffffff;
        }
        .badge-aman {
            background: #10b981;
            color: #ffffff;
        }
        .badge-fast {
            background: #f3e8ff;
            color: #6b21a8;
            border: 0.5px solid #d8b4fe;
        }
        .badge-medium {
            background: #e0f2fe;
            color: #0369a1;
            border: 0.5px solid #7dd3fc;
        }
        .badge-slow {
            background: #f1f5f9;
            color: #475569;
            border: 0.5px solid #cbd5e1;
        }
        .footer-notes {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 8.5px;
            color: #475569;
            margin-bottom: 20px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5px;
        }
        .signature-space {
            height: 45px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Rekapitulasi Dynamic Buffer & Early Warning System (EWS)</h1>
        <p>Sistem Pengendalian Persediaan Berbasis Single Exponential Smoothing (SES), Lead Time 7 Hari, dan FSN Analysis</p>
    </div>

    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td style="width: 15%;"><strong>Target Lokasi</strong></td>
                <td style="width: 35%;">: {{ $lokasiLabel }}</td>
                <td style="width: 15%;"><strong>Target Siklus Restok</strong></td>
                <td style="width: 35%;">: {{ $targetHari }} Hari Ke Depan (Maksimal 30 Hari)</td>
            </tr>
            <tr>
                <td><strong>Filter Status</strong></td>
                <td>: {{ ucfirst($statusFilter) }}</td>
                <td><strong>Tanggal & Waktu Cetak</strong></td>
                <td>: {{ $printedAt }}</td>
            </tr>
        </table>
    </div>

    <!-- Ringkasan Metrik -->
    <table class="summary-grid">
        <tr>
            <td class="summary-card">
                <div class="summary-title">Total Item Dimonitor</div>
                <div class="summary-val">{{ $items->count() }}</div>
            </td>
            <td class="summary-card kritis">
                <div class="summary-title" style="color: #b91c1c;">Status Kritis (Trigger EWS)</div>
                <div class="summary-val" style="color: #dc2626;">{{ $kritisCount }}</div>
            </td>
            <td class="summary-card aman">
                <div class="summary-title" style="color: #15803d;">Status Aman (Buffer Cukup)</div>
                <div class="summary-val" style="color: #16a34a;">{{ $amanCount }}</div>
            </td>
            <td class="summary-card restok">
                <div class="summary-title" style="color: #4338ca;">Total Rekomendasi Restok</div>
                <div class="summary-val" style="color: #4f46e5;">+{{ number_format($totalRekomendasi, 0, ',', '.') }} Unit</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Rekapitulasi EWS -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 13%;">Lokasi</th>
                <th style="width: 23%;">Nama Barang & Kategori</th>
                <th style="width: 8%;">Sisa Stok</th>
                <th style="width: 9%;">Tren Harian</th>
                <th style="width: 11%;">Klasifikasi FSN</th>
                <th style="width: 10%;">Batas EWS (7H)</th>
                <th style="width: 11%;">Safety Stock</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 11%;">Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
            <tr class="{{ $item['is_kritis'] ? 'row-kritis' : '' }}">
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item['lokasi_nama'] }}</td>
                <td>
                    <strong>{{ $item['nama_barang'] }}</strong><br>
                    <span style="color: #64748b; font-size: 8px;">{{ $item['kategori'] }} • {{ $item['tipe_barang'] }}</span>
                </td>
                <td class="text-center font-bold" style="{{ $item['is_kritis'] ? 'color: #dc2626;' : '' }}">
                    {{ $item['stok'] }} Unit
                </td>
                <td class="text-center">
                    {{ number_format($item['daily_forecast'], 2) }} /hari
                </td>
                <td class="text-center">
                    @if($item['safety_percent'] >= 30)
                        <span class="badge badge-fast">Fast (30%)</span>
                    @elseif($item['safety_percent'] == 20)
                        <span class="badge badge-medium">Medium (20%)</span>
                    @else
                        <span class="badge badge-slow">Slow (10%)</span>
                    @endif
                </td>
                <td class="text-center font-bold" style="color: #b91c1c;">
                    {{ $item['batas_ews'] }} Unit
                </td>
                <td class="text-center">
                    {{ $item['safety_units'] }} Unit ({{ $item['safety_percent'] }}%)
                </td>
                <td class="text-center">
                    @if($item['is_kritis'])
                        <span class="badge badge-kritis">KRITIS</span>
                    @else
                        <span class="badge badge-aman">AMAN</span>
                    @endif
                </td>
                <td class="text-center font-bold" style="{{ $item['rekomendasi_restok'] > 0 ? 'color: #16a34a;' : 'color: #64748b;' }}">
                    {{ $item['rekomendasi_restok'] > 0 ? '+' . $item['rekomendasi_restok'] . ' Unit' : '0' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 15px; color: #94a3b8;">
                    Tidak ada data persediaan yang sesuai dengan kriteria filter.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Rujukan Ilmiah & Rasionalisasi Metodologi -->
    <div class="footer-notes">
        <strong>Rasionalisasi Formula & Metodologi Penelitian Skripsi:</strong><br>
        1. <strong>Batas EWS (Lead Time Logistik 7 Hari):</strong> Batas = <code>Tren Harian &times; 7 Hari</code>. Alarm stok berbunyi jika sisa stok &le; kebutuhan 7 hari untuk mengantisipasi siklus pengadaan (approval PO, pengemasan, pengiriman kurir/antar-cabang, hingga penataan di rak).<br>
        2. <strong>FSN Analysis (Safety Stock Dinamis):</strong> <em>Fast-Moving</em> (30% buffer, menjaga omset produk laris); <em>Medium-Moving</em> (20% buffer, permintaan stabil normal); <em>Slow-Moving</em> (10% buffer, sengaja ditekan rendah agar kas toko tidak membeku pada <em>dead capital</em>).<br>
        3. <strong>Siklus Maksimal 30 Hari:</strong> Single Exponential Smoothing (SES) dirancang khusus peramalan jangka pendek. Pembatasan target restok maksimal 30 hari menjaga tingkat galat prediksi (MAPE) tetap rendah sesuai standar evaluasi C.D. Lewis (1982).
    </div>

    <!-- Tanda Tangan Dokumen -->
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui & Menyetujui,<br>
                <strong>Owner / Pimpinan Toko</strong>
                <div class="signature-space"></div>
                ( ___________________________ )
            </td>
            <td>
                Dibuat Oleh,<br>
                <strong>Bagian Persediaan & Logistik (Admin Gudang)</strong>
                <div class="signature-space"></div>
                ( ___________________________ )
            </td>
        </tr>
    </table>

</body>
</html>
