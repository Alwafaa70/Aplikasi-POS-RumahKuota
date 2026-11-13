@extends('layouts.app')
@section('title', 'Detail Closing Cabang')

@push('styles')
<style>
    .card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
    .btn { padding: 10px 20px; border-radius: 4px; border: none; cursor: pointer; text-decoration: none; font-size: 14px; display: inline-block; font-weight: bold; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: #27ae60; color: white; }
    .btn-secondary { background: #95a5a6; color: white; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
    .info-item { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #eee; }
    .info-label { font-size: 12px; color: #7f8c8d; font-weight: bold; display: block; margin-bottom: 5px; }
    .info-value { font-size: 16px; color: #2c3e50; font-weight: bold; }
    .section-title { font-size: 18px; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px; margin-top: 30px; }
    
    .app-card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; display: flex; gap: 20px; align-items: flex-start; }
    .app-info { flex: 1; }
    .app-image { width: 150px; height: 150px; object-fit: contain; background: #f1f2f6; border-radius: 8px; border: 1px solid #ddd; }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #34495e; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; font-size: 14px; }
    
    .text-success { color: #27ae60; }
    .text-danger { color: #e74c3c; }
    .text-warning { color: #f39c12; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0; color: #2c3e50;">Detail Closing: {{ $closing->cabang->nama_cabang }}</h2>
    <a href="{{ route('admin.closing.index') }}" class="btn btn-secondary">⬅ Kembali</a>
</div>

@if(session('success'))
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
        {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Tanggal Closing</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($closing->tanggal)->format('d F Y') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Waktu Submit</span>
            <span class="info-value">{{ \Carbon\Carbon::parse($closing->created_at)->format('H:i:s') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Cabang</span>
            <span class="info-value">{{ $closing->cabang->nama_cabang }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Kasir Bertugas</span>
            <span class="info-value">{{ $closing->kasir->name }}</span>
        </div>
    </div>

    <h3 class="section-title">Rekap Uang Fisik (Laci)</h3>
    <div style="display: flex; gap: 20px;">
        <div style="flex: 1;">
            <div class="info-grid" style="grid-template-columns: 1fr;">
                <div class="info-item">
                    <span class="info-label">Uang Aktual (Input Kasir)</span>
                    <span class="info-value">Rp {{ number_format($closing->uang_fisik_aktual, 0, ',', '.') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Uang Sistem (Harusnya)</span>
                    <span class="info-value">Rp {{ number_format($closing->uang_fisik_sistem, 0, ',', '.') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Selisih</span>
                    <span class="info-value">
                        @if($closing->status_fisik == 'balance')
                            <span class="text-success">Balance (0)</span>
                        @elseif($closing->status_fisik == 'plus')
                            <span class="text-primary">Plus Rp {{ number_format(abs($closing->selisih_fisik), 0, ',', '.') }}</span>
                        @else
                            <span class="text-danger">Minus Rp {{ number_format(abs($closing->selisih_fisik), 0, ',', '.') }}</span>
                            @if(($closing->nominal_pengembalian ?? 0) > 0)
                                @if($closing->status_pengembalian_minus == 'dikembalikan')
                                    <div style="font-size: 12px; margin-top: 5px; background: #27ae60; color: white; padding: 3px 8px; border-radius: 4px; display: inline-block;">✅ Lunas Diganti (Rp {{ number_format($closing->nominal_pengembalian, 0, ',', '.') }})</div>
                                @else
                                    <div style="font-size: 12px; margin-top: 5px; background: #f39c12; color: white; padding: 3px 8px; border-radius: 4px; display: inline-block;">⚠️ Dicicil (Rp {{ number_format($closing->nominal_pengembalian, 0, ',', '.') }})</div>
                                @endif
                                <div style="font-size: 12px; font-style: italic; color: #555; margin-top: 5px;">"{{ $closing->catatan_pengembalian }}"</div>
                            @endif
                        @endif
                    </span>
                </div>
                @if($closing->catatan_kasir)
                <div class="info-item">
                    <span class="info-label">Catatan Kasir</span>
                    <span style="font-size: 14px; color: #555; font-style: italic;">"{{ $closing->catatan_kasir }}"</span>
                </div>
                @endif
            </div>
        </div>
        @if($closing->bukti_uang_fisik)
        <div style="flex: 1; text-align: center; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
            <span class="info-label">Bukti Foto Uang Fisik</span>
            <a href="{{ Storage::url($closing->bukti_uang_fisik) }}" target="_blank">
                <img src="{{ Storage::url($closing->bukti_uang_fisik) }}" alt="Bukti Uang Fisik" style="max-width: 100%; max-height: 250px; border-radius: 4px; border: 1px solid #ddd; margin-top: 10px;">
            </a>
            <p style="font-size: 12px; color: #7f8c8d; margin-top: 5px;">Klik gambar untuk memperbesar</p>
        </div>
        @else
        <div style="flex: 1; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc;">
            <span style="color: #7f8c8d; font-size: 14px;">Tidak ada bukti foto uang fisik</span>
        </div>
        @endif
    </div>

    <h3 class="section-title">Rekap Saldo Dompet Digital</h3>
    @forelse($closing->details as $detail)
    <div class="app-card">
        <div class="app-info">
            <div style="font-size: 18px; font-weight: bold; color: #2c3e50; margin-bottom: 10px;">
                {{ $detail->masterAplikasiLayanan->nama_layanan }}
            </div>
            <div class="info-grid" style="gap: 10px;">
                <div>
                    <span class="info-label">Saldo Aktual (Input Kasir)</span>
                    <span class="info-value">Rp {{ number_format($detail->saldo_aktual, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="info-label">Saldo Sistem (Harusnya)</span>
                    <span class="info-value">Rp {{ number_format($detail->saldo_sistem, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="info-label">Selisih</span>
                    <span class="info-value">
                        @if($detail->status == 'balance')
                            <span class="text-success">Balance</span>
                        @elseif($detail->status == 'plus')
                            <span class="text-primary">Plus Rp {{ number_format(abs($detail->selisih), 0, ',', '.') }}</span>
                        @else
                            <span class="text-danger">Minus Rp {{ number_format(abs($detail->selisih), 0, ',', '.') }}</span>
                            @if(($detail->nominal_pengembalian ?? 0) > 0)
                                @if($detail->status_pengembalian_minus == 'dikembalikan')
                                    <div style="font-size: 11px; margin-top: 5px; background: #27ae60; color: white; padding: 2px 6px; border-radius: 4px; display: inline-block;">✅ Lunas Diganti (Rp {{ number_format($detail->nominal_pengembalian, 0, ',', '.') }})</div>
                                @else
                                    <div style="font-size: 11px; margin-top: 5px; background: #f39c12; color: white; padding: 2px 6px; border-radius: 4px; display: inline-block;">⚠️ Dicicil (Rp {{ number_format($detail->nominal_pengembalian, 0, ',', '.') }})</div>
                                @endif
                                <div style="font-size: 11px; font-style: italic; color: #555; margin-top: 5px;">"{{ $detail->catatan_pengembalian }}"</div>
                            @endif
                        @endif
                    </span>
                </div>
            </div>
        </div>
        
        <div>
            @if($detail->bukti_saldo)
            <a href="{{ Storage::url($detail->bukti_saldo) }}" target="_blank">
                <img src="{{ Storage::url($detail->bukti_saldo) }}" class="app-image" alt="Bukti Saldo">
            </a>
            @else
            <div class="app-image" style="display: flex; align-items: center; justify-content: center;">
                <span style="color: #999; font-size: 12px; text-align: center;">Tidak ada foto</span>
            </div>
            @endif
        </div>
    </div>
    @empty
    <p style="color: #7f8c8d; font-style: italic;">Tidak ada data dompet digital.</p>
    @endforelse

    <h3 class="section-title">Evaluasi & Konfirmasi Admin</h3>
    @if($closing->status_konfirmasi == 'dikonfirmasi')
        <div style="background: #e8f8f5; border: 1px solid #27ae60; padding: 20px; border-radius: 8px;">
            <div style="color: #27ae60; font-weight: bold; font-size: 16px; margin-bottom: 10px;">✅ Laporan Sudah Dikonfirmasi</div>
            @if($closing->catatan_admin)
                <div class="info-label">Catatan Admin:</div>
                <div style="font-size: 14px; color: #2c3e50;">{{ $closing->catatan_admin }}</div>
            @endif
        </div>
    @else
        <form action="{{ route('admin.closing.konfirmasi', $closing->id) }}" method="POST" style="background: #fdfefe; border: 1px solid #e1e8ed; padding: 20px; border-radius: 8px;">
            @csrf
            <div class="form-group">
                <label>Catatan Admin (Opsional)</label>
                <textarea name="catatan_admin" rows="3" class="form-control" placeholder="Tuliskan catatan evaluasi jika ada yang janggal atau salah..."></textarea>
                <p style="font-size: 12px; color: #7f8c8d; margin-top: 5px;">Catatan ini akan terlihat sebagai riwayat evaluasi.</p>
            </div>
            <div style="text-align: right;">
                <button type="submit" class="btn btn-success" onclick="return confirm('Yakin ingin mengonfirmasi laporan closing ini?');">✅ Konfirmasi Laporan Closing</button>
            </div>
        </form>
    @endif
</div>
@endsection
