@extends('layouts.app')
@section('title', 'Cek & Konfirmasi Closing Cabang')

@push('styles')
<style>
    .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .btn { padding: 8px 16px; border-radius: 4px; border: none; cursor: pointer; text-decoration: none; font-size: 14px; display: inline-block; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-danger { background: #e74c3c; color: white; }
    .table-container { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background-color: #f8f9fa; color: #333; font-weight: bold; }
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; }
    .badge-warning { background-color: #f39c12; }
    .badge-success { background-color: #27ae60; }
</style>
@endpush

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #2c3e50;">Daftar Closing Cabang</h2>
    </div>

    <!-- Filter & Cetak -->
    <form action="{{ route('admin.closing.index') }}" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 20px;">
        <div>
            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Mulai Tanggal</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div>
            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        <div>
            <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Cabang</label>
            <select name="cabang_id" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; min-width: 150px;">
                <option value="all">Semua Cabang</option>
                @foreach($cabangs as $cabang)
                    <option value="{{ $cabang->id }}" {{ request('cabang_id') == $cabang->id ? 'selected' : '' }}>{{ $cabang->nama_cabang }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="background: #3498db;">Terapkan Filter</button>
            <a href="{{ route('admin.closing.index') }}" class="btn" style="background: #95a5a6; color: white;">Reset</a>
            <button type="submit" formaction="{{ route('admin.closing.pdf') }}" formtarget="_blank" class="btn btn-danger">🖨️ Cetak PDF</button>
        </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Cabang</th>
                    <th>Kasir</th>
                    <th>Uang Fisik</th>
                    <th>Status Selisih Fisik</th>
                    <th>Status Konfirmasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($closings as $closing)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($closing->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $closing->cabang->nama_cabang }}</td>
                        <td>{{ $closing->kasir->name }}</td>
                        <td>Rp {{ number_format($closing->uang_fisik_aktual, 0, ',', '.') }}</td>
                        <td>
                            @if($closing->status_fisik == 'balance')
                                <span style="color: #27ae60; font-weight: bold;">Balance</span>
                            @elseif($closing->status_fisik == 'plus')
                                <span style="color: #2980b9; font-weight: bold;">Plus Rp {{ number_format(abs($closing->selisih_fisik), 0, ',', '.') }}</span>
                            @else
                                <span style="color: #e74c3c; font-weight: bold;">Minus Rp {{ number_format(abs($closing->selisih_fisik), 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($closing->status_konfirmasi == 'menunggu_konfirmasi')
                                <span class="badge badge-warning">Menunggu Cek</span>
                            @else
                                <span class="badge badge-success">Selesai</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.closing.show', $closing->id) }}" class="btn btn-primary" style="background: #2c3e50; padding: 4px 10px; font-size: 12px;">Cek Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: #7f8c8d;">Belum ada data closing.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 15px;">
        {{ $closings->links() }}
    </div>
</div>
@endsection
