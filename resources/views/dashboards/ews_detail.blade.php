@extends('layouts.app')
@section('title', 'Detail EWS & Restok')

@section('content')
@push('styles')
<style>
    .ews-container { max-width: 900px; margin: 0 auto; }
    .ews-header { margin-bottom: 24px; background: white; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
    .ews-title { font-size: 24px; font-weight: 900; color: #1f2937; margin: 0; }
    .ews-subtitle { font-size: 14px; color: #6b7280; margin-top: 4px; margin-bottom: 0; }
    .btn-back { background: #f3f4f6; color: #4b5563; padding: 8px 16px; border-radius: 8px; font-weight: bold; text-decoration: none; transition: 0.2s; }
    .btn-back:hover { background: #e5e7eb; }
    
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
    @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
    
    .box-info { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .border-danger { border-left: 4px solid #ef4444; }
    .border-normal { border: 1px solid #f3f4f6; }
    
    .item-title { font-size: 18px; font-weight: bold; color: #1f2937; margin: 0 0 8px 0; }
    .item-category { font-size: 14px; color: #6b7280; margin-bottom: 16px; }
    
    .stock-display { display: flex; align-items: flex-end; gap: 12px; }
    .stock-number { font-size: 36px; font-weight: 900; line-height: 1; }
    .text-danger { color: #dc2626; }
    .text-warning { color: #f97316; }
    .stock-label { font-size: 14px; font-weight: bold; color: #6b7280; margin-bottom: 4px; }
    
    .badge { display: inline-block; margin-top: 12px; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-warning { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    
    .ai-title { font-size: 14px; font-weight: bold; color: #6b7280; margin: 0 0 16px 0; }
    .ai-row { display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 1px solid #f3f4f6; }
    .ai-row.no-border { border-bottom: none; padding-bottom: 0; padding-top: 8px; }
    
    .text-sm-gray { font-size: 14px; color: #4b5563; }
    .text-bold-dark { font-weight: bold; color: #1f2937; }
    .text-bold-blue { font-weight: bold; color: #2563eb; }
    .text-bold-green { font-size: 20px; font-weight: 900; color: #16a34a; }
    
    .action-box { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; }
    .action-title { font-size: 18px; font-weight: bold; color: #1f2937; margin: 0 0 12px 0; }
    .action-desc { font-size: 14px; color: #4b5563; margin-bottom: 24px; line-height: 1.5; }
    
    .btn-primary-large { width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; font-weight: bold; border: none; cursor: pointer; transition: 0.2s; font-size: 16px; }
    .btn-primary-large:hover { opacity: 0.9; transform: translateY(-1px); }
    @media (min-width: 768px) { .btn-primary-large { width: auto; } }
</style>
@endpush

@section('content')
<div class="ews-container">
    <!-- Header -->
    <div class="ews-header">
        <div>
            <h1 class="ews-title">Detail Peringatan Stok (EWS)</h1>
            <p class="ews-subtitle">Sistem Peramalan SES (Simple Exponential Smoothing)</p>
        </div>
        <a href="{{ route('kasir.dashboard') }}" class="btn-back">
            Kembali
        </a>
    </div>

    <!-- Informasi Barang -->
    <div class="grid-2">
        <div class="box-info border-danger">
            <h3 class="item-title">{{ $barang->nama_barang }}</h3>
            <div class="item-category">Kategori: {{ $barang->tipe_barang }}</div>
            
            <div class="stock-display">
                <div class="stock-number {{ $jumlahStok <= 0 ? 'text-danger' : 'text-warning' }}">
                    {{ $jumlahStok }}
                </div>
                <div class="stock-label">Unit Tersisa</div>
            </div>
            
            @if($jumlahStok <= 0)
                <span class="badge badge-danger">Stok Kosong!</span>
            @else
                <span class="badge badge-warning">Stok Menipis!</span>
            @endif
        </div>

        <div class="box-info border-normal">
            <h3 class="ai-title">Rekomendasi AI (Untuk {{ $rangeDays }} Hari Ke Depan, s/d {{ $targetDate }})</h3>
            
            <div>
                <div class="ai-row">
                    <span class="text-sm-gray">Prediksi Penjualan:</span>
                    <span class="text-bold-dark">{{ $ramalan }} Unit</span>
                </div>
                <div class="ai-row">
                    <span class="text-sm-gray" title="Persentase cadangan stok yang diatur oleh Owner">Safety Stock (Jaga-jaga {{ $safetyStockPercent }}%):</span>
                    <span class="text-bold-blue">+ {{ $safetyStock }} Unit</span>
                </div>
                <div class="ai-row no-border">
                    <span style="font-size: 14px; font-weight: bold; color: #1f2937;">Total Kebutuhan Restok:</span>
                    <span class="text-bold-green">{{ $rekomendasiRestok }} Unit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rasionalisasi EWS Akademis & Bisnis -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 24px; font-size: 13px; color: #334155;">
        <h4 style="margin: 0 0 8px 0; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-circle-info" style="color: #3b82f6;"></i> Rasionalisasi & Perhitungan Otomatis Sistem EWS:
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-top: 10px;">
            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <b style="color: #dc2626; display: block; margin-bottom: 4px;">1. Batas EWS (Lead Time 7 Hari)</b>
                Peringatan darurat berbunyi jika sisa stok &le; kebutuhan 7 hari. 7 hari adalah estimasi waktu pengiriman barang dari Gudang Pusat ke Cabang agar toko tidak keburu kehabisan stok.
            </div>
            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <b style="color: #d97706; display: block; margin-bottom: 4px;">2. Cadangan Safety Stock ({{ $safetyStockPercent }}%)</b>
                Cadangan ekstra otomatis dihitung proporsional dari tren penjualan untuk menahan lonjakan pembeli mendadak tanpa menimbun modal kas berlebih di cabang.
            </div>
            <div style="background: white; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <b style="color: #16a34a; display: block; margin-bottom: 4px;">3. Target Siklus (Maks 30 Hari)</b>
                Sistem membatasi peramalan maksimal 30 hari (1 bulan operasional) untuk menjaga akurasi algoritma SES dan menghindari penumpukan barang yang memicu modal macet.
            </div>
        </div>
    </div>

    <!-- Minta Bantuan Restok -->
    <div class="action-box">
        <h2 class="action-title">Tindakan Restok</h2>
        <p class="action-desc">
            Berdasarkan algoritma peramalan data masa lalu, sistem memprediksi Anda akan membutuhkan penambahan stok sebanyak <strong style="color: #1f2937;">{{ $rekomendasiRestok }} unit</strong> agar stok aman untuk melayani pembeli selama <b>{{ $rangeDays }} hari ke depan</b> (sampai dengan <b>{{ $targetDate }}</b>).<br><br>
            Tekan tombol di bawah untuk mengirim permohonan restok secara otomatis ke Gudang Pusat. Admin Pusat akan mengevaluasi dan memproses pengiriman.
        </p>

        <form action="{{ route('kasir.ews.minta_bantuan') }}" method="POST">
            @csrf
            <input type="hidden" name="barang_id" value="{{ $barang->id }}">
            <button type="submit" class="btn-primary-large">
                <svg xmlns="http://www.w3.org/2000/svg" style="height: 20px; width: 20px;" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M8.707 7.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l2-2a1 1 0 00-1.414-1.414L11 7.586V3a1 1 0 10-2 0v4.586l-.293-.293z" />
                    <path d="M3 5a2 2 0 012-2h1a1 1 0 010 2H5v7h2l1 2h4l1-2h2V5h-1a1 1 0 110-2h1a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" />
                </svg>
                Minta Bantuan Restok Sekarang
            </button>
        </form>
    </div>
</div>
@endsection
