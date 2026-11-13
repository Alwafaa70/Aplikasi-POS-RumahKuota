@extends('layouts.app')
@section('title', 'Dashboard Admin Pusat')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    @keyframes pulseRed {
        0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
        70%  { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .dash-fade { animation: fadeInUp 0.35s ease both; }

    .pulse-badge {
        animation: pulseRed 2s infinite;
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(229, 231, 235, 0.8);
    }

    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.08);
    }
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6" x-data="{
    filterLokasi: 'semua',
    searchQuery: '',
    matchSearch(name) {
        if (!this.searchQuery) return true;
        return name.toLowerCase().includes(this.searchQuery.toLowerCase());
    }
}">

    {{-- ───────── HEADER & USER GREETING ───────── --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-2xl border border-gray-200/80 shadow-xs dash-fade">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center text-2xl shadow-md">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-gray-900 tracking-tight">Selamat Datang, {{ Auth::user()->name }} 👋</h1>
                    <span class="bg-blue-100 text-blue-800 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">Admin Pusat</span>
                </div>
                <p class="text-gray-500 text-xs md:text-sm mt-0.5">
                    <i class="fa-regular fa-calendar-days text-gray-400 mr-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} &bull; Pusat Distribusi & Manajemen Logistik Multi-Cabang
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full md:w-auto">
            <a href="{{ route('admin.pembelian.create') }}" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition hover-lift">
                <i class="fa-solid fa-cart-plus"></i>
                <span>+ Buat PO Supplier</span>
            </a>
            <a href="{{ route('distribusi.mutasi.index') }}?tab=mutasi" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition hover-lift">
                <i class="fa-solid fa-dolly"></i>
                <span>+ Kirim Mutasi</span>
            </a>
        </div>
    </div>

    {{-- ───────── ALERT NOTIFIKASI CEPAT ───────── --}}
    <div class="flex flex-wrap gap-3 dash-fade" style="animation-delay: 0.05s;">
        @if($totalPendingRequest > 0)
        <a href="{{ route('admin.request_operasional.index') }}" class="flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition">
            <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
            <i class="fa-solid fa-bell"></i>
            <span><strong class="text-rose-900 text-sm font-black">{{ $totalPendingRequest }}</strong> Request Cabang Menunggu</span>
        </a>
        @endif

        @if($totalPendingClosing > 0)
        <a href="{{ route('admin.closing.index') }}" class="flex items-center gap-2 px-4 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition">
            <i class="fa-solid fa-cash-register"></i>
            <span><strong class="text-amber-900 text-sm font-black">{{ $totalPendingClosing }}</strong> Closing Menunggu Konfirmasi</span>
        </a>
        @endif

        @if($totalDraftMutasiStok + $totalDraftMutasiCash > 0)
        <a href="{{ route('distribusi.mutasi.index') }}?tab=draft" class="flex items-center gap-2 px-4 py-2 bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 rounded-xl text-xs font-bold transition">
            <i class="fa-solid fa-truck-ramp-box"></i>
            <span><strong class="text-sky-900 text-sm font-black">{{ $totalDraftMutasiStok + $totalDraftMutasiCash }}</strong> Draft Mutasi Tertunda</span>
        </a>
        @endif

        @if($totalEwsAll > 0)
        <span class="flex items-center gap-2 px-4 py-2 bg-red-100 text-red-800 border border-red-300 rounded-xl text-xs font-bold">
            <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
            <span><strong class="text-red-900 text-sm font-black">{{ $totalEwsAll }}</strong> Barang Kritis EWS Perlu Restok</span>
        </span>
        @else
        <span class="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-bold">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>Seluruh Stok Berada di Atas Ambang Batas EWS (Aman)</span>
        </span>
        @endif
    </div>

    {{-- ───────── BANNER RISET SES & MONITOR BUFFER EWS ───────── --}}
    <div class="rounded-2xl p-6 text-white relative overflow-hidden shadow-lg hover-lift dash-fade" style="background: linear-gradient(135deg, #1e3a8a 0%, #4338ca 50%, #6d28d9 100%); animation-delay: 0.1s;">
        <div class="absolute -right-6 -bottom-8 opacity-10 pointer-events-none">
            <i class="fa-solid fa-brain text-9xl"></i>
        </div>
        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="flex items-center gap-4 md:gap-5">
                <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-3xl text-white shrink-0 shadow-inner">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-black uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-microchip"></i> Sistem Cerdas Terintegrasi
                    </div>
                    <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">Laboratorium SES & Buffer EWS AI</h2>
                    <p class="text-indigo-100/90 text-xs md:text-sm mt-1 max-w-2xl leading-relaxed">
                        Sistem Early Warning System (EWS) menghitung ambang batas otomatis berdasarkan peramalan Single Exponential Smoothing (SES), laju konsumsi riil harian, dan lead time logistik 7 hari.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto shrink-0">
                <a href="{{ route('owner.ews.index') }}" class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-indigo-900 font-extrabold text-xs shadow-md hover:bg-indigo-50 transition">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Sistem Monitor Buffer EWS</span>
                </a>
                <a href="{{ route('owner.ses_debugger') }}" class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-extrabold text-xs border border-white/30 backdrop-blur-sm transition">
                    <i class="fa-solid fa-flask"></i>
                    <span>Laboratorium Riset SES</span>
                </a>
            </div>
        </div>
    </div>

    {{-- ───────── MENU AKSES CEPAT UTAMA (GRID) ───────── --}}
    <div class="dash-fade" style="animation-delay: 0.15s;">
        <h3 class="text-xs font-black uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-2">
            <i class="fa-solid fa-bolt text-amber-500"></i>
            <span>Menu Operasional Gudang Pusat</span>
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
            <a href="{{ route('admin.pembelian.index') }}" class="bg-white p-4 rounded-xl border border-gray-200/80 hover:border-blue-400 text-center hover-lift group shadow-xs">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-boxes-packing"></i>
                </div>
                <div class="font-extrabold text-xs text-gray-800 group-hover:text-blue-600 transition-colors">Penerimaan PO</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Stok Masuk Supplier</div>
            </a>

            <a href="{{ route('distribusi.mutasi.index') }}" class="bg-white p-4 rounded-xl border border-gray-200/80 hover:border-indigo-400 text-center hover-lift group shadow-xs relative">
                @if($totalDraftMutasiStok + $totalDraftMutasiCash > 0)
                    <span class="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center pulse-badge">
                        {{ $totalDraftMutasiStok + $totalDraftMutasiCash }}
                    </span>
                @endif
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-dolly"></i>
                </div>
                <div class="font-extrabold text-xs text-gray-800 group-hover:text-indigo-600 transition-colors">Distribusi Mutasi</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Kirim ke Cabang</div>
            </a>

            <a href="{{ route('admin.request_operasional.index') }}" class="bg-white p-4 rounded-xl border border-gray-200/80 hover:border-rose-400 text-center hover-lift group shadow-xs relative">
                @if($totalPendingRequest > 0)
                    <span class="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center pulse-badge">
                        {{ $totalPendingRequest }}
                    </span>
                @endif
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div class="font-extrabold text-xs text-gray-800 group-hover:text-rose-600 transition-colors">Request Cabang</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Permintaan Bantuan</div>
            </a>

            <a href="{{ route('admin.closing.index') }}" class="bg-white p-4 rounded-xl border border-gray-200/80 hover:border-amber-400 text-center hover-lift group shadow-xs relative">
                @if($totalPendingClosing > 0)
                    <span class="absolute top-2 right-2 bg-amber-600 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center">
                        {{ $totalPendingClosing }}
                    </span>
                @endif
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
                <div class="font-extrabold text-xs text-gray-800 group-hover:text-amber-600 transition-colors">Verifikasi Closing</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Setoran Kasir Cabang</div>
            </a>

            <a href="{{ route('admin.dompet_pusat.index') }}" class="bg-white p-4 rounded-xl border border-gray-200/80 hover:border-emerald-400 text-center hover-lift group shadow-xs">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="font-extrabold text-xs text-gray-800 group-hover:text-emerald-600 transition-colors">Dompet Pusat</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Kas & E-Wallet</div>
            </a>

            <a href="{{ route('master.barang.index') }}" class="bg-white p-4 rounded-xl border border-gray-200/80 hover:border-purple-400 text-center hover-lift group shadow-xs">
                <div class="w-10 h-10 mx-auto mb-2 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-barcode"></i>
                </div>
                <div class="font-extrabold text-xs text-gray-800 group-hover:text-purple-600 transition-colors">Master Barang</div>
                <div class="text-[11px] text-gray-400 mt-0.5">Katalog & Harga</div>
            </a>
        </div>
    </div>

    {{-- ───────── SECTION UTAMA: EARLY WARNING SYSTEM (EWS) COMMAND CENTER ───────── --}}
    <div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm overflow-hidden dash-fade" style="animation-delay: 0.2s;">
        
        {{-- Section Header --}}
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-red-50/60 via-white to-indigo-50/40">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-black uppercase tracking-wider mb-1.5">
                        <i class="fa-solid fa-triangle-exclamation"></i> Early Warning System Monitoring
                    </div>
                    <h2 class="text-xl md:text-2xl font-black text-gray-900 tracking-tight">
                        Peringatan EWS Stok Kritis & Akses Cepat Restok
                    </h2>
                    <p class="text-gray-500 text-xs md:text-sm mt-0.5">
                        Pemantauan stok barang fisik yang telah menyentuh batas aman (lead time 7 hari) dilengkapi rekomendasi restok dan mutasi instan.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('owner.ews.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-xl font-bold text-xs transition">
                        <span>Buka Buffer EWS Lengkap</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            {{-- 4 KPI Metric Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                {{-- Gudang Pusat Kritis --}}
                <div class="bg-white p-4 rounded-2xl border {{ $totalBarangKritisGudang > 0 ? 'border-red-300 bg-red-50/30' : 'border-gray-200' }} shadow-xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">🏢 Gudang Pusat</span>
                        @if($totalBarangKritisGudang > 0)
                            <span class="w-2.5 h-2.5 rounded-full bg-red-600 animate-ping"></span>
                        @else
                            <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                        @endif
                    </div>
                    <div class="text-2xl font-black {{ $totalBarangKritisGudang > 0 ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $totalBarangKritisGudang }} <span class="text-xs font-bold text-gray-500">Produk Kritis</span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px]">
                        <span class="text-gray-400">Tindakan Restok:</span>
                        <a href="{{ route('admin.pembelian.create') }}" class="font-bold text-emerald-600 hover:text-emerald-800">+ Buat PO Supplier</a>
                    </div>
                </div>

                {{-- Cabang Kritis --}}
                <div class="bg-white p-4 rounded-2xl border {{ $totalCabangKritis > 0 ? 'border-amber-300 bg-amber-50/30' : 'border-gray-200' }} shadow-xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">🏪 Seluruh Cabang</span>
                        @if($totalCabangKritis > 0)
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping"></span>
                        @else
                            <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                        @endif
                    </div>
                    <div class="text-2xl font-black {{ $totalCabangKritis > 0 ? 'text-amber-600' : 'text-gray-800' }}">
                        {{ $totalCabangKritis }} <span class="text-xs font-bold text-gray-500">Cabang ({{ $totalBarangKritisCabang }} Produk)</span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-[11px]">
                        <span class="text-gray-400">Tindakan Restok:</span>
                        <a href="{{ route('distribusi.mutasi.index') }}?tab=mutasi" class="font-bold text-indigo-600 hover:text-indigo-800">+ Kirim Mutasi</a>
                    </div>
                </div>

                {{-- Total Rekomendasi Restok --}}
                <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">📦 Total Restok AI</span>
                        <i class="fa-solid fa-boxes-stacked text-blue-500 text-sm"></i>
                    </div>
                    <div class="text-2xl font-black text-gray-900">
                        +{{ number_format($totalUnitRestokDibutuhkan ?? 0, 0, ',', '.') }} <span class="text-xs font-bold text-gray-500">Unit Fisik</span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-100 text-[11px] text-gray-400">
                        Perhitungan kebutuhan 30 hari + Safety Stock
                    </div>
                </div>

                {{-- Status Keamanan Stok --}}
                <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">⏱ Lead Time</span>
                        <i class="fa-solid fa-clock-rotate-left text-purple-500 text-sm"></i>
                    </div>
                    <div class="text-2xl font-black text-indigo-700">
                        7 Hari <span class="text-xs font-bold text-gray-500">Ambang Batas</span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-gray-100 text-[11px] text-gray-400">
                        Rumus: Batas = Tren SES &times; 7 Hari
                    </div>
                </div>
            </div>

            {{-- Filter & Search Toolbar --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mt-6 pt-5 border-t border-gray-200/80">
                <div class="inline-flex rounded-xl p-1 bg-gray-100/90 text-xs font-bold">
                    <button type="button" 
                            @click="filterLokasi = 'semua'" 
                            :class="filterLokasi === 'semua' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800'" 
                            class="px-3.5 py-1.5 rounded-lg transition">
                        Semua Lokasi ({{ count($ewsAlerts) }})
                    </button>
                    <button type="button" 
                            @click="filterLokasi = 'pusat'" 
                            :class="filterLokasi === 'pusat' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800'" 
                            class="px-3.5 py-1.5 rounded-lg transition">
                        🏢 Gudang Pusat ({{ $totalBarangKritisGudang }})
                    </button>
                    <button type="button" 
                            @click="filterLokasi = 'cabang'" 
                            :class="filterLokasi === 'cabang' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-800'" 
                            class="px-3.5 py-1.5 rounded-lg transition">
                        🏪 Cabang ({{ $totalCabangKritis }})
                    </button>
                </div>

                <div class="relative w-full sm:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari produk kritis..." 
                           class="w-full pl-9 pr-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                </div>
            </div>

        </div>

        {{-- Section Body: Grouped Cards per Location --}}
        <div class="p-6 space-y-6">
            @if(count($ewsAlerts) > 0)
                @foreach($ewsAlerts as $lokasi => $items)
                @php
                    $isGudangPusat = ($lokasi === 'Gudang Pusat');
                    $lokasiType = $isGudangPusat ? 'pusat' : 'cabang';
                    $cabangId = $items[0]->cabang_id ?? null;
                @endphp
                <div x-show="(filterLokasi === 'semua' || filterLokasi === '{{ $lokasiType }}')" 
                     class="bg-gray-50/80 rounded-2xl border border-gray-200/90 overflow-hidden shadow-2xs hover:border-gray-300 transition">
                    
                    {{-- Lokasi Header --}}
                    <div class="px-5 py-3.5 bg-white border-b border-gray-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl {{ $isGudangPusat ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700' }} flex items-center justify-center text-sm font-black">
                                <i class="fa-solid {{ $isGudangPusat ? 'fa-building' : 'fa-store' }}"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                                    <span>{{ $lokasi }}</span>
                                    <span class="text-[11px] bg-red-100 text-red-700 font-extrabold px-2.5 py-0.5 rounded-full">
                                        {{ count($items) }} Produk Kritis
                                    </span>
                                </h3>
                                <p class="text-[11px] text-gray-400">
                                    {{ $isGudangPusat ? 'Stok Induk Distribusi Utama' : 'Stok Cabang Operasional Kasir' }}
                                </p>
                            </div>
                        </div>

                        {{-- Quick Mass Action Header --}}
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            @if($isGudangPusat)
                                <a href="{{ route('admin.pembelian.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-xs transition">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    <span>Buka Form PO Supplier</span>
                                </a>
                            @else
                                <a href="{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_tujuan={{ $cabangId }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold text-xs shadow-xs transition">
                                    <i class="fa-solid fa-truck-ramp-box"></i>
                                    <span>Buka Mutasi ke Cabang Ini</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Data Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs">
                            <thead class="bg-gray-100 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-4 py-3 text-left">Nama Produk</th>
                                    <th class="px-3 py-3 text-center">Sisa Stok</th>
                                    <th class="px-3 py-3 text-center">Batas EWS</th>
                                    <th class="px-3 py-3 text-center">Tren & Klasifikasi FSN</th>
                                    <th class="px-3 py-3 text-center bg-emerald-50 text-emerald-800">Rekomendasi Restok</th>
                                    <th class="px-3 py-3 text-center">Audit Rumus</th>
                                    <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Aksi Cepat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach($items as $item)
                                <tr class="hover:bg-red-50/30 transition" x-show="matchSearch('{{ addslashes($item->nama_barang) }}')">
                                    {{-- Nama Produk --}}
                                    <td class="px-4 py-3">
                                        <div class="font-extrabold text-gray-900 text-sm">{{ $item->nama_barang }}</div>
                                        <div class="text-[11px] text-gray-400 font-medium flex items-center gap-1.5 mt-0.5">
                                            <span>{{ $item->kategori ?? 'Umum' }}</span>
                                            <span>&bull;</span>
                                            <span class="inline-block px-1.5 py-0.2 rounded bg-gray-100 text-gray-600 text-[10px] font-bold">{{ $item->tipe_barang }}</span>
                                        </div>
                                    </td>

                                    {{-- Sisa Stok --}}
                                    <td class="px-3 py-3 text-center">
                                        <span class="inline-flex items-center justify-center px-3 py-1 text-xs font-black text-white bg-red-600 rounded-full animate-pulse shadow-xs">
                                            {{ $item->stok_saat_ini }} Unit
                                        </span>
                                    </td>

                                    {{-- Batas EWS --}}
                                    <td class="px-3 py-3 text-center">
                                        <span class="bg-red-50 border border-red-200 text-red-700 font-extrabold px-2.5 py-1 rounded-lg text-xs inline-block">
                                            &le; {{ $item->batas_ews }} Unit
                                        </span>
                                        <div class="text-[10px] text-gray-400 mt-0.5">Lead Time 7 Hari</div>
                                    </td>

                                    {{-- Tren & Klasifikasi --}}
                                    <td class="px-3 py-3 text-center">
                                        <div class="font-bold text-gray-800">{{ number_format($item->daily_forecast, 2) }} /hari</div>
                                        @php
                                            $badgeClass = 'bg-gray-100 text-gray-700 border-gray-200';
                                            if ($item->klasifikasi_badge === 'fast') {
                                                $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
                                            } elseif ($item->klasifikasi_badge === 'medium') {
                                                $badgeClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                            }
                                        @endphp
                                        <span class="inline-block mt-0.5 text-[10px] font-bold px-2 py-0.5 rounded-md border {{ $badgeClass }}">
                                            {{ $item->klasifikasi_nama }}
                                        </span>
                                    </td>

                                    {{-- Rekomendasi Restok --}}
                                    <td class="px-3 py-3 text-center bg-emerald-50/40">
                                        @if($item->rekomendasi_restok > 0)
                                            <span class="bg-emerald-100 text-emerald-800 border border-emerald-300 font-black px-3 py-1 rounded-xl text-xs inline-block shadow-2xs">
                                                +{{ $item->rekomendasi_restok }} Unit
                                            </span>
                                            <div class="text-[10px] text-emerald-700 mt-0.5 font-medium">Kebutuhan 30 Hari + Buffer</div>
                                        @else
                                            <span class="text-xs text-gray-400 font-semibold bg-gray-100 px-2 py-0.5 rounded">Cukup</span>
                                        @endif
                                    </td>

                                    {{-- Bedah Rumus --}}
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <button type="button" 
                                                onclick="window.openEwsStatsModal({{ $item->barang_id }}, {{ $item->cabang_id ? $item->cabang_id : 'null' }}, 30)" 
                                                class="bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 border border-indigo-200 px-2.5 py-1.5 rounded-xl font-bold text-xs inline-flex items-center gap-1.5 transition shadow-2xs active:scale-95">
                                            <i class="fa-solid fa-chart-pie"></i>
                                            <span>Bedah Rumus</span>
                                        </button>
                                    </td>

                                    {{-- Aksi Cepat Restok / Mutasi (DENGAN QUERY PRE-FILL YANG FIX) --}}
                                    <td class="px-4 py-3 text-center whitespace-nowrap bg-blue-50/30">
                                        @if($isGudangPusat)
                                            {{-- Restok Gudang Pusat: Pesan PO Supplier --}}
                                            <a href="{{ route('admin.pembelian.create') }}?barang_id={{ $item->barang_id }}&qty={{ $item->rekomendasi_restok }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-xs transition hover:scale-105 active:scale-95">
                                                <i class="fa-solid fa-cart-shopping"></i>
                                                <span>Pesan PO (+{{ $item->rekomendasi_restok }})</span>
                                            </a>
                                        @else
                                            {{-- Restok Cabang: Kirim Mutasi Stok dari Gudang Pusat ke Cabang --}}
                                            <a href="{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_tujuan={{ $item->cabang_id }}&barang_id={{ $item->barang_id }}&qty={{ $item->rekomendasi_restok }}" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-xs transition hover:scale-105 active:scale-95">
                                                <i class="fa-solid fa-truck"></i>
                                                <span>Kirim Mutasi (+{{ $item->rekomendasi_restok }})</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            @else
                <div class="p-12 text-center bg-emerald-50/50 rounded-2xl border border-emerald-200">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 class="text-lg font-black text-emerald-800">Semua Stok Berada dalam Batas Aman!</h3>
                    <p class="text-sm text-emerald-600 max-w-md mx-auto mt-1">
                        Tidak ada barang fisik di Gudang Pusat maupun Cabang yang menyentuh batas kritis EWS. Pengadaan logistik berjalan stabil.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ───────── SECTION 2: RINGKASAN MONITORING STOK & ASET FISIK SELURUH LOKASI ───────── --}}
    <div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm overflow-hidden dash-fade" style="animation-delay: 0.25s;">
        <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-blue-600"></i>
                    <span>Ringkasan Nilai Aset Fisik & Stok Per Lokasi</span>
                </h3>
                <p class="text-gray-500 text-xs mt-0.5">
                    Rincian komprehensif aset barang fisik yang tersimpan di Gudang Pusat dan seluruh cabang aktif.
                </p>
            </div>
            <a href="{{ route('distribusi.mutasi.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs transition">
                <span>Buka Monitoring Mutasi & Aset</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Lokasi Gudang / Cabang</th>
                        <th class="px-4 py-3.5 text-center">Status EWS Kritis</th>
                        <th class="px-5 py-3.5 text-right">Total Nilai Aset Fisik</th>
                        <th class="px-5 py-3.5 text-center">Tindakan Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    {{-- Baris Gudang Pusat --}}
                    <tr class="bg-blue-50/40 hover:bg-blue-50/70 transition">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <div>
                                    <div class="font-black text-gray-900 text-sm">Gudang Pusat (Utama)</div>
                                    <div class="text-[11px] text-gray-500">Pusat Pengadaan & Stok Induk</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($gudangPusat_has_ews)
                                <button type="button" 
                                        onclick='showEwsModal("Gudang Pusat", @json($gudangPusat_ews_items), null)'
                                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-600 hover:bg-red-700 text-white font-extrabold rounded-full text-xs shadow-xs transition">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>{{ $gudangPusat_ews_count }} Item Kritis</span>
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-full text-xs">
                                    <i class="fa-solid fa-check"></i> Semua Aman
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-black text-blue-900 text-sm">
                            Rp {{ number_format($gudangPusat_total_fisik_value, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('distribusi.mutasi.index') }}" class="px-3 py-1.5 bg-gray-800 hover:bg-black text-white font-bold rounded-lg text-xs transition">
                                    Monitoring
                                </a>
                                <a href="{{ route('admin.pembelian.create') }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition">
                                    + Beli PO
                                </a>
                            </div>
                        </td>
                    </tr>

                    {{-- Baris Tiap Cabang --}}
                    @foreach($cabangs as $cabang)
                    <tr class="hover:bg-gray-50/80 transition {{ $cabang->has_ews ? 'bg-red-50/20' : '' }}">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $cabang->has_ews ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center text-sm font-bold">
                                    <i class="fa-solid fa-store"></i>
                                </div>
                                <div>
                                    <div class="font-extrabold text-gray-900 text-sm">{{ $cabang->nama_cabang }}</div>
                                    @if($cabang->area)
                                        <div class="text-[11px] text-gray-400">{{ $cabang->area }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($cabang->has_ews)
                                <button type="button" 
                                        onclick='showEwsModal("{{ addslashes($cabang->nama_cabang) }}", @json($cabang->ews_items), {{ $cabang->id }})'
                                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-600 hover:bg-red-700 text-white font-extrabold rounded-full text-xs shadow-xs transition">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>{{ $cabang->ews_count }} Item Kritis</span>
                                </button>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 text-emerald-700 font-bold rounded-full text-xs">
                                    <i class="fa-solid fa-check"></i> Aman
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right font-extrabold text-gray-800 text-sm">
                            Rp {{ number_format($cabang->total_fisik_value, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" 
                                        onclick='showDetailStok("{{ addslashes($cabang->nama_cabang) }}", @json($cabang->all_stok))'
                                        class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold rounded-lg text-xs transition">
                                    Lihat Stok
                                </button>
                                <a href="{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_tujuan={{ $cabang->id }}" 
                                   class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-bold rounded-lg text-xs transition">
                                    Mutasi
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @if($cabangs->isEmpty())
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-gray-400">Belum ada data cabang.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ───────── MODAL POPUP EWS CEPAT ───────── --}}
<div id="ewsModal" style="display:none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-3xl rounded-3xl shadow-2xl overflow-hidden border border-gray-100 flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-red-600 to-rose-700 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-black tracking-wider text-red-200">Peringatan EWS Stok Kritis</div>
                    <h3 id="ewsModalTitle" class="text-lg font-black text-white">Daftar Barang Kritis</h3>
                </div>
            </div>
            <button onclick="closeEwsModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center font-bold text-lg transition">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto bg-gray-50 flex-1">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-100 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama Produk</th>
                            <th class="px-3 py-3 text-center">Stok Kini</th>
                            <th class="px-3 py-3 text-center">Batas EWS</th>
                            <th class="px-3 py-3 text-center">Rekomendasi</th>
                            <th class="px-3 py-3 text-center">Audit Rumus</th>
                            <th class="px-4 py-3 text-center">Aksi Restok</th>
                        </tr>
                    </thead>
                    <tbody id="ewsModalBody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <div class="px-6 py-3.5 bg-white border-t border-gray-200 flex items-center justify-between text-xs text-gray-500">
            <span>Rekomendasi dihitung otomatis berdasarkan SES (Lead Time 7 Hari + Buffer).</span>
            <button onclick="closeEwsModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-bold transition">Tutup</button>
        </div>
    </div>
</div>

{{-- ───────── MODAL SEMUA STOK CABANG ───────── --}}
<div id="stokModal" style="display:none;" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden border border-gray-100 flex flex-col max-h-[90vh]">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-black tracking-wider text-blue-200">Monitoring Stok Fisik</div>
                    <h3 id="stokModalTitle" class="text-lg font-black text-white">Stok Barang</h3>
                </div>
            </div>
            <button onclick="closeStokModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center font-bold text-lg transition">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto bg-gray-50 flex-1">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xs">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-100 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama Produk</th>
                            <th class="px-4 py-3 text-center">Jumlah Stok</th>
                        </tr>
                    </thead>
                    <tbody id="stokModalBody" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <div class="px-6 py-3.5 bg-white border-t border-gray-200 flex justify-end">
            <button onclick="closeStokModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-bold text-xs transition">Tutup</button>
        </div>
    </div>
</div>

@include('partials.modal_ews_stats')
@endsection

@push('scripts')
<script>
function showEwsModal(lokasiName, items, cabangId) {
    document.getElementById('ewsModalTitle').innerText = 'Barang Kritis: ' + lokasiName;
    const tbody = document.getElementById('ewsModalBody');
    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada data produk kritis di lokasi ini.</td></tr>';
    } else {
        items.forEach(function(item) {
            const urlAksi = cabangId
                ? `{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_tujuan=${cabangId}&barang_id=${item.barang_id}&qty=${item.rekomendasi_restok || 1}`
                : `{{ route('admin.pembelian.create') }}?barang_id=${item.barang_id}&qty=${item.rekomendasi_restok || 1}`;
            const labelAksi = cabangId ? `🚚 Mutasi (+${item.rekomendasi_restok || 1})` : `🛒 Beli (+${item.rekomendasi_restok || 1})`;
            const btnColor  = cabangId ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-emerald-600 hover:bg-emerald-700';

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition';
            tr.innerHTML = `
                <td class="px-4 py-3 font-extrabold text-gray-900">${item.nama_barang}</td>
                <td class="px-3 py-3 text-center font-black text-red-600">${item.stok_saat_ini} Unit</td>
                <td class="px-3 py-3 text-center font-bold text-gray-600">&le; ${item.threshold || item.batas_ews} Unit</td>
                <td class="px-3 py-3 text-center font-black text-emerald-700 bg-emerald-50/50">+${item.rekomendasi_restok || 1} Unit</td>
                <td class="px-3 py-3 text-center whitespace-nowrap">
                    <button type="button" onclick="window.openEwsStatsModal(${item.barang_id}, ${cabangId || 'null'}, 30)" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 border border-indigo-200 rounded-lg font-bold text-[11px] transition inline-flex items-center gap-1">
                        <i class="fa-solid fa-chart-pie"></i> Bedah Rumus
                    </button>
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <a href="${urlAksi}" class="inline-flex items-center justify-center px-3 py-1.5 ${btnColor} text-white font-bold rounded-lg text-xs shadow-xs transition">
                        ${labelAksi}
                    </a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('ewsModal').style.display = 'flex';
}

function closeEwsModal() {
    document.getElementById('ewsModal').style.display = 'none';
}

function showDetailStok(lokasiName, items) {
    document.getElementById('stokModalTitle').innerText = lokasiName;
    const tbody = document.getElementById('stokModalBody');
    tbody.innerHTML = '';

    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="2" class="px-4 py-8 text-center text-gray-400">Tidak ada stok fisik tercatat.</td></tr>';
    } else {
        items.forEach(function(item) {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50 transition';
            tr.innerHTML = `
                <td class="px-4 py-3 font-extrabold text-gray-800">${item.nama_barang}</td>
                <td class="px-4 py-3 text-center font-black text-gray-900">${item.jumlah_stok} pcs</td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('stokModal').style.display = 'flex';
}

function closeStokModal() {
    document.getElementById('stokModal').style.display = 'none';
}
</script>
@endpush
