<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Owner Dashboard</title>
    <style>
        :root { --primary: #8e44ad; --bg: #f4f7f6; --card: #ffffff; --text: #333; --border: #ddd; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: var(--bg); color: var(--text); }
        .header { background: var(--primary); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .header button { background: #e74c3c; border: none; color: white; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;}
        .container { padding: 30px; max-width: 1000px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;}
        .card { background: var(--card); padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid var(--border); }
        
        .form-inline { display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px;}
        .form-group { flex: 1; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;}
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-filter { background: #3498db; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; height: 38px;}
        .btn-export-pdf { background: #e74c3c; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; border: none; cursor: pointer;}
        .btn-export-excel { background: #2ecc71; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; border: none; cursor: pointer;}

        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid var(--border); padding: 10px; text-align: left; font-size: 14px; }
        .table th { background: #f9f9f9; font-weight: bold; }
        .total-row { font-weight: bold; background: #fdfdfd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Keuangan & Omzet</h1>
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="{{ route('owner.dashboard') }}" style="color: white; text-decoration: none; font-weight: bold; font-size: 14px;">Dashboard</a>
            <a href="{{ route('karyawan.index') }}" style="color: white; text-decoration: none; font-weight: bold; font-size: 14px;">Karyawan</a>
            <a href="{{ route('laporan.index') }}" style="color: white; text-decoration: none; font-weight: bold; font-size: 14px;">Laporan</a>
            <span>| Halo, {{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-top: 0; border-bottom: 2px solid var(--primary); padding-bottom: 10px;">Filter Laporan</h2>
            <form method="GET" action="{{ route('laporan.index') }}" class="form-inline">
                <div class="form-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label>Cabang</label>
                    <select name="cabang_id">
                        <option value="">Semua Cabang</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id }}" {{ isset($filters['cabang_id']) && $filters['cabang_id'] == $cabang->id ? 'selected' : '' }}>
                                {{ $cabang->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="margin: 0;">Pratinjau Omzet</h2>
                <div style="display: flex; gap: 10px;">
                    <form action="{{ route('laporan.export.pdf') }}" method="GET" style="margin: 0;">
                        <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                        <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                        <input type="hidden" name="cabang_id" value="{{ $filters['cabang_id'] ?? '' }}">
                        <button type="submit" class="btn-export-pdf">Download PDF</button>
                    </form>
                    <form action="{{ route('laporan.export.excel') }}" method="GET" style="margin: 0;">
                        <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                        <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                        <input type="hidden" name="cabang_id" value="{{ $filters['cabang_id'] ?? '' }}">
                        <button type="submit" class="btn-export-excel">Download Excel</button>
                    </form>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Tanggal</th>
                        <th>Cabang</th>
                        <th>Kasir</th>
                        <th>Metode Bayar</th>
                        <th>Total Tagihan (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sum = 0; @endphp
                    @foreach($transaksis as $trx)
                    @php $sum += $trx->total_tagihan; @endphp
                    <tr>
                        <td>#{{ $trx->id }}</td>
                        <td>{{ $trx->tanggal_transaksi }}</td>
                        <td>{{ $trx->cabang ? $trx->cabang->nama_cabang : '-' }}</td>
                        <td>{{ $trx->kasir ? $trx->kasir->name : '-' }}</td>
                        <td>{{ strtoupper($trx->metode_pembayaran) }}</td>
                        <td style="text-align: right;">{{ number_format($trx->total_tagihan, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    @if($transaksis->isEmpty())
                    <tr>
                        <td colspan="6" style="text-align: center;">Tidak ada data transaksi.</td>
                    </tr>
                    @endif
                </tbody>
                @if(!$transaksis->isEmpty())
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" style="text-align: right;">TOTAL HALAMAN INI:</td>
                        <td style="text-align: right; color: var(--primary);">Rp {{ number_format($sum, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
            
            <div style="margin-top: 15px;">
                {{ $transaksis->withQueryString()->links() }}
            </div>
        </div>
    </div>
</body>
</html>
