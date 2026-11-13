@extends('layouts.app')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* Styling scrollbar halus untuk tab navigasi */
    .scrollbar-thin::-webkit-scrollbar {
        height: 6px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 9999px;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 9999px;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 bg-gray-50 min-h-screen" x-data="ewsDashboard()">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header & Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600 shadow-inner">
                        <i class="fa-solid fa-bell text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-800 tracking-tight">Manajemen Dynamic Buffer EWS</h1>
                        <p class="text-gray-500 text-sm mt-0.5">Sistem Pengendalian Persediaan Otomatis & Rekomendasi Restok Berbasis AI per Lokasi</p>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a :href="'{{ route('owner.ews.export_pdf') }}?target_hari=' + targetHari + '&lokasi=' + filterLokasi + '&status=' + filterStatus" 
                   target="_blank" 
                   class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold py-2.5 px-4 rounded-xl flex items-center gap-2 border border-rose-200 transition shadow-2xs">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Cetak Rekap Buffer (PDF)</span>
                </a>
                <a href="{{ route('owner.ses_debugger') }}" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-2.5 px-4 rounded-xl flex items-center gap-2 border border-indigo-200 transition shadow-2xs">
                    <i class="fa-solid fa-flask"></i>
                    <span>Laboratorium Uji SES </span>
                </a>
                <form action="{{ route('owner.ews.recalculate') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Sistem akan menghitung ulang Batas Buffer EWS seluruh barang fisik berdasarkan tren penjualan AI terbaru. Lanjutkan?')" 
                            class="bg-blue-600 hover:bg-blue-700 active:scale-95 transition text-white font-bold py-2.5 px-4 rounded-xl flex items-center gap-2 shadow-sm">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Kalkulasi Ulang Buffer AI</span>
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl shadow-sm flex items-start gap-3" role="alert">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mt-0.5"></i>
                <div>
                    <p class="font-bold">Berhasil Diperbarui!</p>
                    <p class="text-sm text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Item Dimonitor</span>
                    <h3 class="text-2xl font-black text-gray-800 mt-1" x-text="items.length">0</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Gudang & Seluruh Cabang</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-cubes"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-wider">Status Kritis (Trigger EWS)</span>
                    <h3 class="text-2xl font-black text-red-600 mt-1" x-text="kritisCount">0</h3>
                    <p class="text-xs text-red-400 mt-0.5">Stok &le; Batas Lead Time</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Status Aman (Buffer Cukup)</span>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1" x-text="amanCount">0</h3>
                    <p class="text-xs text-emerald-500 mt-0.5">Stok &gt; Batas Lead Time</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Total Rekomendasi Restok</span>
                    <h3 class="text-2xl font-black text-indigo-600 mt-1" x-text="totalRekomendasi + ' Unit'">0 Unit</h3>
                    <p class="text-xs text-indigo-400 mt-0.5">Untuk Target <span x-text="targetHari"></span> Hari</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-cart-flatbed"></i>
                </div>
            </div>
        </div>

        <!-- Filter & Parameter Panel -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 space-y-4">
            <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-end">
                <!-- Search Box -->
                <div class="flex-1">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Cari Produk Fisik / Kategori</label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder="Ketik nama produk atau kategori..." 
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none text-sm font-medium">
                    </div>
                </div>

                <!-- Lokasi Filter Dropdown -->
                <div class="w-full lg:w-56">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Pilih Lokasi</label>
                    <select x-model="filterLokasi" class="w-full py-2.5 px-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none text-sm font-semibold text-gray-700 bg-white">
                        <option value="all">📁 Semua Lokasi (Tabel Terpisah)</option>
                        <option value="gudang_0">🏢 Gudang Pusat</option>
                        @foreach($cabangs as $c)
                            <option value="cabang_{{ $c->id }}">🏪 Cabang {{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-full lg:w-48">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Filter Status</label>
                    <select x-model="filterStatus" class="w-full py-2.5 px-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none text-sm font-semibold text-gray-700 bg-white">
                        <option value="all">Semua Status</option>
                        <option value="kritis">🔴 Kritis / Butuh Restok</option>
                        <option value="aman">🟢 Stok Aman</option>
                    </select>
                </div>

                <!-- Target Hari Restok Dinamis -->
                <div class="w-full lg:w-72">
                    <label class="block text-xs font-bold text-blue-700 uppercase tracking-wider mb-2">
                        Target Hari Restok Kedepan
                    </label>
                    <div class="relative">
                        <input type="number" 
                               x-model.number="targetHari" 
                               min="1" 
                               max="30"
                               class="w-full border-2 border-blue-400 bg-blue-50/50 rounded-xl py-2 px-3 pr-14 focus:border-blue-600 focus:bg-white outline-none text-base font-black text-blue-700">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-blue-500 pointer-events-none">Hari</span>
                    </div>
                    <div x-show="targetHari > 30" class="text-[11px] text-amber-700 font-bold mt-1 bg-amber-50 p-1.5 rounded-lg border border-amber-200">
                        ⚠️ Peramalan SES optimal untuk jangka pendek (maks 30 hari). Lebih dari itu berisiko galat & modal macet.
                    </div>
                </div>
            </div>

            <!-- Preset Buttons for Target Hari -->
            <div class="flex items-center gap-2 pt-2 border-t border-gray-100 flex-wrap">
                <span class="text-xs font-bold text-gray-400 mr-1">Preset Hari Restok (Maks 30 Hari):</span>
                <button type="button" @click="targetHari = 7" :class="targetHari === 7 ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded-lg text-xs font-bold transition">7 Hari (1 Mgg)</button>
                <button type="button" @click="targetHari = 14" :class="targetHari === 14 ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded-lg text-xs font-bold transition">14 Hari (Setengah Bln)</button>
                <button type="button" @click="targetHari = 21" :class="targetHari === 21 ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded-lg text-xs font-bold transition">21 Hari (3 Mgg)</button>
                <button type="button" @click="targetHari = 30" :class="targetHari === 30 ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200'" class="px-3 py-1 rounded-lg text-xs font-bold transition">⭐ 30 Hari (1 Bulan Penuh)</button>
            </div>
        </div>

        <!-- Formula Guide Card -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-5 text-sm text-blue-900 shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-info text-blue-600 text-lg mt-0.5"></i>
                <div class="space-y-1 w-full">
                    <p class="font-bold text-blue-950 text-base">Rasionalisasi & Cara Kerja Sistem Buffer EWS Otomatis:</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2 text-xs">
                        <div class="bg-white/90 p-3 rounded-xl border border-blue-100 shadow-xs">
                            <span class="font-extrabold text-red-600 block mb-1">1. Batas EWS (Kenapa Lead Time 7 Hari?)</span>
                            <span class="text-gray-600 leading-relaxed block">
                                Rumus: <code>Tren Harian &times; 7 Hari</code>. Lead Time 7 hari adalah estimasi waktu siklus logistik: persetujuan PO, packing supplier, pengiriman kurir/area, hingga unboxing di cabang. Jika stok &le; kebutuhan 7 hari, alarm darurat menyala agar toko tidak keburu kosong total (stockout).
                            </span>
                        </div>
                        <div class="bg-white/90 p-3 rounded-xl border border-blue-100 shadow-xs">
                            <span class="font-extrabold text-amber-600 block mb-1">2. Safety Stock Dinamis (10%, 20%, 30%)</span>
                            <span class="text-gray-600 leading-relaxed block">
                                Disesuaikan dengan kecepatan perputaran (FSN Analysis):
                                <br>&bull; <b>Laris (Fast): 30%</b> &rarr; Cegah kehilangan omset saat lonjakan pembeli.
                                <br>&bull; <b>Stabil (Medium): 20%</b> &rarr; Cadangan standar operasional.
                                <br>&bull; <b>Lambat (Slow): 10%</b> &rarr; Sengaja ditekan kecil agar modal kas tidak tertimbun (uang mati) & cegah voucher kadaluarsa.
                            </span>
                        </div>
                        <div class="bg-white/90 p-3 rounded-xl border border-blue-100 shadow-xs">
                            <span class="font-extrabold text-emerald-700 block mb-1">3. Rekomendasi Restok (Kenapa Maks 30 Hari?)</span>
                            <span class="text-gray-600 leading-relaxed block">
                                Rumus: <code>(Target Hari &times; Tren + Safety Stock) - Sisa Stok</code>. 
                                <br>Peramalan Single Exponential Smoothing (SES) dirancang khusus untuk <b>jangka pendek (maksimal 30 hari)</b>. Meramal di atas 30 hari meningkatkan risiko galat peramalan dan memicu penumpukan stok yang mematikan arus kas (cash flow) toko.
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB NAVIGASI LOKASI DI ATAS (SCROLLABLE & INTERAKTIF) -->
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot text-indigo-600"></i>
                    <span>Pilih Lokasi Inventaris:</span>
                </span>
                <span class="text-xs text-gray-400 font-medium hidden sm:inline">
                    Klik tab untuk fokus ke lokasi tertentu, atau pilih "Semua Lokasi" untuk melihat seluruh tabel lokasi.
                </span>
            </div>
            
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                <!-- Tab: Semua Lokasi -->
                <button type="button" 
                        @click="filterLokasi = 'all'"
                        :class="filterLokasi === 'all' 
                            ? 'bg-slate-900 text-white shadow-md ring-2 ring-slate-900/20' 
                            : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'"
                        class="px-4 py-2.5 rounded-xl font-bold text-xs shrink-0 flex items-center gap-2 transition cursor-pointer">
                    <i class="fa-solid fa-layer-group text-sm"></i>
                    <span>Semua Lokasi</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                          :class="filterLokasi === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                          x-text="locations.length + ' Lokasi'"></span>
                </button>
                
                <!-- Tab: Tiap Lokasi (Gudang Pusat & Cabang 1 s/d 9) -->
                <template x-for="loc in locations" :key="loc.key">
                    <button type="button" 
                            @click="filterLokasi = loc.key"
                            :class="filterLokasi === loc.key 
                                ? (loc.tipe === 'Gudang Pusat' ? 'bg-indigo-600 text-white shadow-md ring-2 ring-indigo-600/30' : 'bg-blue-600 text-white shadow-md ring-2 ring-blue-600/30') 
                                : 'bg-white text-slate-700 hover:bg-slate-50 border border-slate-200'"
                            class="px-4 py-2.5 rounded-xl font-bold text-xs shrink-0 flex items-center gap-2 transition cursor-pointer">
                        <i :class="loc.tipe === 'Gudang Pusat' ? 'fa-solid fa-warehouse' : 'fa-solid fa-store'"></i>
                        <span x-text="loc.nama"></span>
                        
                        <!-- Badge Kritis jika ada stok kritis di lokasi ini -->
                        <template x-if="getLocKritisCount(loc.key) > 0">
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse"
                                  x-text="getLocKritisCount(loc.key) + ' Kritis'"></span>
                        </template>
                    </button>
                </template>
            </div>
        </div>

        <!-- DAFTAR TABEL PER LOKASI (HEADER DI ATAS & SCROLL MANDIRI PER LOKASI) -->
        <div class="space-y-6">
            <template x-for="group in groupedLocations" :key="group.key">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition hover:shadow-md">
                    
                    <!-- 1. HEADER KARTU LOKASI DI ATAS TABEL -->
                    <div class="p-5 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4"
                         :class="group.tipe === 'Gudang Pusat' ? 'bg-gradient-to-r from-indigo-50/80 via-white to-indigo-50/30' : 'bg-gradient-to-r from-blue-50/80 via-white to-blue-50/30'">
                        
                        <!-- Info Lokasi & KPI Ringkas -->
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg text-white shadow-sm"
                                     :class="group.tipe === 'Gudang Pusat' ? 'bg-indigo-600' : 'bg-blue-600'">
                                    <i :class="group.tipe === 'Gudang Pusat' ? 'fa-solid fa-warehouse' : 'fa-solid fa-store'"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-lg font-black text-gray-900 tracking-tight" x-text="group.nama"></h2>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider"
                                              :class="group.tipe === 'Gudang Pusat' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700'"
                                              x-text="group.tipe"></span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Pemantauan batas buffer EWS dan cadangan safety stock dinamis
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Badge Summary & Aksi Cepat per Lokasi -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Total Item -->
                            <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-white border border-gray-200 text-gray-700 shadow-2xs">
                                Total: <b class="text-gray-900" x-text="group.items.length"></b> Item
                            </span>

                            <!-- Kritis Badge -->
                            <template x-if="group.kritisCount > 0">
                                <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-rose-50 border border-rose-200 text-rose-700 shadow-2xs flex items-center gap-1.5 animate-pulse">
                                    <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
                                    <span x-text="group.kritisCount + ' Item Kritis'"></span>
                                </span>
                            </template>
                            <template x-if="group.kritisCount === 0">
                                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-2xs flex items-center gap-1.5">
                                    <i class="fa-solid fa-shield-halved text-[11px]"></i>
                                    <span>Semua Stok Aman</span>
                                </span>
                            </template>

                            <!-- Total Rekomendasi Restok -->
                            <span class="px-3 py-1.5 rounded-xl text-xs font-black bg-emerald-100 border border-emerald-200 text-emerald-800 shadow-2xs">
                                Restok: <span x-text="'+ ' + group.totalRestock + ' Unit'"></span>
                            </span>

                            <!-- Tombol Aksi Cepat Sesuai Tipe Lokasi -->
                            <template x-if="group.tipe === 'Gudang Pusat'">
                                <div class="flex items-center gap-2">
                                    <template x-if="group.kritisCount > 0">
                                        <a :href="'{{ route('admin.pembelian.create') }}?items=' + encodeURIComponent(JSON.stringify(group.items.filter(i => calculateRestock(i) > 0).map(i => ({barang_id: i.barang_id, qty: calculateRestock(i)}))))" 
                                           class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition inline-flex items-center gap-1.5 active:scale-95"
                                           title="Pesan seluruh barang kritis Gudang Pusat sekaligus ke supplier">
                                            <i class="fa-solid fa-cart-plus text-[11px]"></i>
                                            <span>Beli Semua Kritis (PO)</span>
                                        </a>
                                    </template>
                                    <a href="{{ route('admin.pembelian.create') }}" 
                                       class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 shadow-2xs transition inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                        <span>Buat PO Baru</span>
                                    </a>
                                </div>
                            </template>
                            <template x-if="group.tipe !== 'Gudang Pusat'">
                                <a :href="'{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_asal=&cabang_tujuan=' + group.cabang_id" 
                                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition inline-flex items-center gap-1.5 active:scale-95">
                                    <i class="fa-solid fa-truck-fast text-[11px]"></i>
                                    <span>Kirim Mutasi ke Cabang Ini</span>
                                </a>
                            </template>
                        </div>
                    </div>

                    <!-- 2. TABEL KHUSUS LOKASI INI DENGAN SCROLL MANDIRI (TANPA KOLOM LOKASI) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[850px]">
                            <thead>
                                <tr class="bg-gray-800 text-white text-xs font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4">Barang Fisik & Kategori</th>
                                    <th class="py-3 px-3 text-center">Stok Saat Ini</th>
                                    <th class="py-3 px-3 text-center">Tren Harian (AI) & FSN</th>
                                    <th class="py-3 px-3 text-center bg-red-700/80">Batas EWS (Lead Time)</th>
                                    <th class="py-3 px-3 text-center bg-amber-600/80">Safety Stock (Cadangan)</th>
                                    <th class="py-3 px-3 text-center">Status</th>
                                    <th class="py-3 px-4 text-center bg-emerald-700/80">Rekomendasi Restok</th>
                                    <th class="py-3 px-3 text-center">Aksi Cepat Restok & Audit</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-gray-100">
                                <template x-for="item in group.items" :key="item.lokasi_key + '_' + item.barang_id">
                                    <tr class="hover:bg-blue-50/40 transition">
                                        <!-- Nama Barang & Kategori -->
                                        <td class="py-3 px-4">
                                            <div class="font-extrabold text-gray-900" x-text="item.nama_barang"></div>
                                            <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                                                <span class="inline-block bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded text-[10px] font-semibold" x-text="item.kategori"></span>
                                                <span class="text-gray-300">•</span>
                                                <span class="text-[11px]" x-text="item.tipe_barang"></span>
                                            </div>
                                        </td>

                                        <!-- Stok Saat Ini -->
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <span class="text-base font-black font-mono" 
                                                  :class="item.stok <= item.batas_ews ? 'text-red-600' : 'text-gray-800'" 
                                                  x-text="item.stok + ' Pcs'"></span>
                                        </td>

                                        <!-- Tren Penjualan Harian & Klasifikasi FSN -->
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <div class="font-bold text-gray-800 font-mono text-xs">
                                                <span x-text="parseFloat(item.daily_forecast).toFixed(2)"></span>
                                                <span class="text-[10px] text-gray-400 font-normal"> /hari</span>
                                            </div>
                                            <div class="mt-0.5">
                                                <template x-if="item.safety_percent >= 30">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black bg-purple-100 text-purple-700 border border-purple-200">
                                                        🚀 Laris (Fast)
                                                    </span>
                                                </template>
                                                <template x-if="item.safety_percent === 20">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                                        ⚖️ Stabil (Medium)
                                                    </span>
                                                </template>
                                                <template x-if="item.safety_percent <= 10">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                                        🐢 Lambat (Slow)
                                                    </span>
                                                </template>
                                            </div>
                                        </td>

                                        <!-- Batas EWS -->
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <span class="bg-red-50 text-red-700 border border-red-200 font-black px-2.5 py-0.5 rounded-lg text-xs font-mono" 
                                                  x-text="item.batas_ews + ' Pcs'"></span>
                                            <div class="text-[10px] text-gray-400 mt-0.5" x-text="'(7 Hari × Tren)'"></div>
                                        </td>

                                        <!-- Safety Stock Dinamis -->
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <span class="bg-amber-50 text-amber-700 border border-amber-200 font-black px-2.5 py-0.5 rounded-lg text-xs font-mono" 
                                                  x-text="calculateSafety(item) + ' Pcs'"></span>
                                            <div class="text-[10px] font-bold mt-0.5" 
                                                 :class="item.safety_percent >= 30 ? 'text-purple-600' : (item.safety_percent === 20 ? 'text-blue-600' : 'text-gray-500')" 
                                                 x-text="'Buffer: ' + item.safety_percent + '%'"></div>
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <template x-if="item.stok <= item.batas_ews">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-red-100 text-red-700 border border-red-200 animate-pulse">
                                                    <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> Kritis
                                                </span>
                                            </template>
                                            <template x-if="item.stok > item.batas_ews">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <i class="fa-solid fa-check text-[10px]"></i> Aman
                                                </span>
                                            </template>
                                        </td>

                                        <!-- Rekomendasi Restok -->
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <template x-if="calculateRestock(item) > 0">
                                                <span class="bg-emerald-100 text-emerald-800 border border-emerald-300 font-black py-1 px-3 rounded-xl text-xs font-mono shadow-2xs inline-block" 
                                                      x-text="'+ ' + calculateRestock(item) + ' Pcs'"></span>
                                            </template>
                                            <template x-if="calculateRestock(item) === 0">
                                                <span class="text-xs text-gray-400 font-semibold bg-gray-100 px-2.5 py-1 rounded-lg">Cukup</span>
                                            </template>
                                        </td>

                                        <!-- Aksi Cepat Restok, Audit Stats AI & Lab SES -->
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <!-- Aksi Cepat Restok Khusus Gudang Pusat (Beli ke Supplier via PO) -->
                                                <template x-if="group.tipe === 'Gudang Pusat' && calculateRestock(item) > 0">
                                                    <a :href="'{{ route('admin.pembelian.create') }}?barang_id=' + item.barang_id + '&qty=' + calculateRestock(item)" 
                                                       class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-xl font-black text-xs inline-flex items-center gap-1 transition shadow-2xs active:scale-95"
                                                       title="Pesan barang ini ke supplier via PO dengan rekomendasi restok">
                                                        <i class="fa-solid fa-cart-plus text-xs"></i>
                                                        <span>Pesan PO</span>
                                                    </a>
                                                </template>

                                                <!-- Aksi Cepat Restok Khusus Cabang (Kirim Mutasi dari Gudang Pusat) -->
                                                <template x-if="group.tipe !== 'Gudang Pusat' && calculateRestock(item) > 0">
                                                    <a :href="'{{ route('distribusi.mutasi.index') }}?tab=mutasi&cabang_asal=&cabang_tujuan=' + group.cabang_id + '&barang_id=' + item.barang_id + '&qty=' + calculateRestock(item)" 
                                                       class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded-xl font-black text-xs inline-flex items-center gap-1 transition shadow-2xs active:scale-95"
                                                       title="Kirim mutasi stok dari gudang pusat ke cabang ini sesuai rekomendasi restok">
                                                        <i class="fa-solid fa-truck-fast text-xs"></i>
                                                        <span>Mutasi</span>
                                                    </a>
                                                </template>

                                                <button type="button" 
                                                        @click="openStats(item)" 
                                                        class="bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-xl font-bold text-xs inline-flex items-center gap-1 transition shadow-2xs active:scale-95 cursor-pointer"
                                                        title="Lihat Audit Perhitungan & FSN Analysis">
                                                    <i class="fa-solid fa-chart-pie text-xs"></i>
                                                    <span>Stats AI</span>
                                                </button>
                                                <button type="button" 
                                                        @click="openSesLab(item)" 
                                                        class="bg-slate-50 hover:bg-indigo-600 hover:text-white text-gray-700 border border-gray-200 px-2 py-1 rounded-xl font-bold text-xs inline-flex items-center gap-1 transition shadow-2xs active:scale-95 cursor-pointer"
                                                        title="Uji Langsung di Laboratorium SES (Alpha 0.1 - 0.9)">
                                                    <i class="fa-solid fa-flask text-xs"></i>
                                                    <span>Lab SES</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Empty State dalam tabel lokasi jika tidak ada item yang cocok -->
                                <tr x-show="group.items.length === 0">
                                    <td colspan="8" class="p-8 text-center text-gray-400 font-medium">
                                        <i class="fa-solid fa-box-open text-2xl mb-1 text-gray-300 block"></i>
                                        Tidak ada produk yang cocok dengan filter pencarian pada lokasi ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer per Lokasi -->
                    <div class="p-3 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
                        <span x-text="'Menampilkan ' + group.items.length + ' item barang fisik di ' + group.nama"></span>
                        <span class="text-gray-400 text-[11px]">Formula Restok: (Target ' + targetHari + ' Hari × Tren + Safety) - Sisa Stok</span>
                    </div>

                </div>
            </template>

            <!-- Global Empty State jika pencarian tidak menemukan lokasi manapun -->
            <div x-show="groupedLocations.length === 0" class="bg-white rounded-2xl p-12 text-center text-gray-400 border border-gray-200 shadow-sm">
                <i class="fa-solid fa-magnifying-glass text-4xl mb-3 text-gray-300 block"></i>
                <h3 class="text-base font-bold text-gray-700">Tidak ada data yang cocok</h3>
                <p class="text-xs text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian, filter lokasi, atau status stok.</p>
                <button type="button" 
                        @click="searchQuery = ''; filterLokasi = 'all'; filterStatus = 'all'" 
                        class="mt-4 px-4 py-2 bg-blue-50 text-blue-700 font-bold text-xs rounded-xl hover:bg-blue-100 transition">
                    Reset Semua Filter
                </button>
            </div>
        </div>

    </div>
</div>

<script>
function ewsDashboard() {
    return {
        targetHari: 30,
        searchQuery: '',
        filterLokasi: 'all',
        filterStatus: 'all',
        items: @json($allEws),

        // Daftar unik seluruh lokasi (Gudang Pusat + Cabang 1 s/d 9)
        locations: [
            { key: 'gudang_0', nama: 'Gudang Pusat', tipe: 'Gudang Pusat', cabang_id: '' },
            @foreach($cabangs as $c)
                { key: 'cabang_{{ $c->id }}', nama: 'Cabang {{ $c->nama_cabang }}', tipe: 'Cabang', cabang_id: '{{ $c->id }}' },
            @endforeach
        ],

        get kritisCount() {
            return this.items.filter(item => item.stok <= item.batas_ews).length;
        },

        get amanCount() {
            return this.items.filter(item => item.stok > item.batas_ews).length;
        },

        get totalRekomendasi() {
            return this.items.reduce((sum, item) => sum + this.calculateRestock(item), 0);
        },

        // Helper untuk menghitung jumlah item kritis pada satu lokasi spesifik
        getLocKritisCount(lokasiKey) {
            return this.items.filter(item => item.lokasi_key === lokasiKey && item.stok <= item.batas_ews).length;
        },

        // Data yang dikelompokkan per lokasi untuk menghasilkan tabel mandiri per lokasi
        get groupedLocations() {
            let targetLocs = this.locations;

            // Jika filter lokasi bukan 'all', tampilkan hanya lokasi yang dipilih
            if (this.filterLokasi !== 'all') {
                targetLocs = targetLocs.filter(l => l.key === this.filterLokasi);
            }

            return targetLocs.map(loc => {
                let locItems = this.items.filter(item => item.lokasi_key === loc.key);

                // Filter Pencarian Barang / Kategori
                if (this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase();
                    locItems = locItems.filter(item => {
                        const matchNama = item.nama_barang.toLowerCase().includes(q);
                        const matchKategori = item.kategori.toLowerCase().includes(q);
                        return matchNama || matchKategori;
                    });
                }

                // Filter Status Kritis / Aman
                if (this.filterStatus === 'kritis') {
                    locItems = locItems.filter(item => item.stok <= item.batas_ews);
                } else if (this.filterStatus === 'aman') {
                    locItems = locItems.filter(item => item.stok > item.batas_ews);
                }

                const kritisCount = locItems.filter(item => item.stok <= item.batas_ews).length;
                const amanCount = locItems.filter(item => item.stok > item.batas_ews).length;
                const totalRestock = locItems.reduce((sum, item) => sum + this.calculateRestock(item), 0);

                return {
                    key: loc.key,
                    nama: loc.nama,
                    tipe: loc.tipe,
                    cabang_id: loc.cabang_id || '',
                    items: locItems,
                    kritisCount: kritisCount,
                    amanCount: amanCount,
                    totalRestock: totalRestock
                };
            }).filter(group => {
                // Sembunyikan grup jika hasil filter pencarian kosong (hanya saat melakukan pencarian aktif)
                if (this.searchQuery.trim() !== '' || this.filterStatus !== 'all') {
                    return group.items.length > 0;
                }
                return true;
            });
        },

        calculateSafety(item) {
            let demand = item.daily_forecast * this.targetHari;
            return Math.ceil(demand * (item.safety_percent / 100));
        },

        calculateRestock(item) {
            let demand = item.daily_forecast * this.targetHari;
            let safety = this.calculateSafety(item);
            let rekomendasi = Math.ceil((demand + safety) - item.stok);
            return rekomendasi > 0 ? rekomendasi : 0;
        },

        openStats(item) {
            let cabangId = (item.lokasi_key && item.lokasi_key.startsWith('cabang_')) 
                ? item.lokasi_key.replace('cabang_', '') 
                : null;
            if (window.openEwsStatsModal) {
                window.openEwsStatsModal(item.barang_id, cabangId, this.targetHari);
            }
        },

        openSesLab(item) {
            let cabangId = (item.lokasi_key && item.lokasi_key.startsWith('cabang_')) 
                ? item.lokasi_key.replace('cabang_', '') 
                : 0;
            window.open(`/owner/ses-debugger?barang_id=${item.barang_id}&cabang_id=${cabangId}`, '_blank');
        }
    };
}
</script>

@include('partials.modal_ews_stats')
@endsection
