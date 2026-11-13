@extends('layouts.app')
@section('title', 'Super Master Barang')

@push('styles')
<style>
    .topbar { background-color: white !important; }
</style>
@endpush

@section('content')
<div class="space-y-6" 
     x-data="masterBarangForm({{ $categoriesTree->toJson() }})"
     x-init="$watch('ranges', value => rangesJson = JSON.stringify(value), {deep: true})">
     
    <!-- Header Title & Quick Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-boxes-stacked text-[11px]"></i>
                    Katalog Master Terpusat
                </span>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                    Multi-Cabang POS
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                Super Master Barang & Layanan
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola seluruh katalog produk fisik (Aksesoris, Voucher) dan digital (PPOB, Tarik Tunai) secara rapi dan terpisah per kategori.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <button @click="kategoriModalOpen = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 font-bold py-2.5 px-4 rounded-xl text-xs sm:text-sm shadow-2xs transition">
                <i class="fa-solid fa-tags text-teal-600"></i>
                <span>Kelola Kategori Induk</span>
            </button>
            <button @click="openTambahModal()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs sm:text-sm shadow-md shadow-indigo-600/20 transition transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Item Baru</span>
            </button>
        </div>
    </div>

    <!-- Quick Stat Cards (Kategori Overview) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- 1. Semua -->
        <div @click="setTab('Semua')" 
             class="cursor-pointer p-4 rounded-2xl border transition-all duration-200"
             :class="activeTab === 'Semua' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-600/20' : 'bg-white hover:bg-slate-50 border-slate-200/80 text-slate-800 shadow-2xs'">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider" :class="activeTab === 'Semua' ? 'text-indigo-200' : 'text-slate-400'">Semua Item</span>
                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs" :class="activeTab === 'Semua' ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600'"><i class="fa-solid fa-boxes-stacked"></i></span>
            </div>
            <div class="text-2xl font-black" :class="activeTab === 'Semua' ? 'text-white' : 'text-slate-900'">{{ $barangs->count() }}</div>
            <div class="text-[11px] mt-1 truncate" :class="activeTab === 'Semua' ? 'text-indigo-100' : 'text-slate-500'">Seluruh Produk Master</div>
        </div>

        <!-- 2. Fisik ACC -->
        @php $cntFisikAcc = $barangs->where('tipe_barang', 'Fisik ACC')->count(); @endphp
        <div @click="setTab('Fisik ACC')" 
             class="cursor-pointer p-4 rounded-2xl border transition-all duration-200"
             :class="activeTab === 'Fisik ACC' ? 'bg-amber-600 text-white border-amber-600 shadow-md shadow-amber-600/20' : 'bg-white hover:bg-slate-50 border-slate-200/80 text-slate-800 shadow-2xs'">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider" :class="activeTab === 'Fisik ACC' ? 'text-amber-200' : 'text-slate-400'">Fisik ACC</span>
                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs" :class="activeTab === 'Fisik ACC' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-600'"><i class="fa-solid fa-plug"></i></span>
            </div>
            <div class="text-2xl font-black" :class="activeTab === 'Fisik ACC' ? 'text-white' : 'text-slate-900'">{{ $cntFisikAcc }}</div>
            <div class="text-[11px] mt-1 truncate" :class="activeTab === 'Fisik ACC' ? 'text-amber-100' : 'text-slate-500'">Aksesoris HP & Fisik</div>
        </div>

        <!-- 3. Fisik Voucher -->
        @php $cntFisikVoucher = $barangs->where('tipe_barang', 'Fisik Voucher Kartu')->count(); @endphp
        <div @click="setTab('Fisik Voucher Kartu')" 
             class="cursor-pointer p-4 rounded-2xl border transition-all duration-200"
             :class="activeTab === 'Fisik Voucher Kartu' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-600/20' : 'bg-white hover:bg-slate-50 border-slate-200/80 text-slate-800 shadow-2xs'">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider" :class="activeTab === 'Fisik Voucher Kartu' ? 'text-blue-200' : 'text-slate-400'">Fisik Voucher</span>
                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs" :class="activeTab === 'Fisik Voucher Kartu' ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600'"><i class="fa-solid fa-sim-card"></i></span>
            </div>
            <div class="text-2xl font-black" :class="activeTab === 'Fisik Voucher Kartu' ? 'text-white' : 'text-slate-900'">{{ $cntFisikVoucher }}</div>
            <div class="text-[11px] mt-1 truncate" :class="activeTab === 'Fisik Voucher Kartu' ? 'text-blue-100' : 'text-slate-500'">Voucher & Perdana</div>
        </div>

        <!-- 4. Digital A -->
        @php $cntDigitalA = $barangs->where('tipe_barang', 'Digital A')->count(); @endphp
        <div @click="setTab('Digital A')" 
             class="cursor-pointer p-4 rounded-2xl border transition-all duration-200"
             :class="activeTab === 'Digital A' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/20' : 'bg-white hover:bg-slate-50 border-slate-200/80 text-slate-800 shadow-2xs'">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider" :class="activeTab === 'Digital A' ? 'text-emerald-200' : 'text-slate-400'">Digital A</span>
                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs" :class="activeTab === 'Digital A' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-600'"><i class="fa-solid fa-bolt"></i></span>
            </div>
            <div class="text-2xl font-black" :class="activeTab === 'Digital A' ? 'text-white' : 'text-slate-900'">{{ $cntDigitalA }}</div>
            <div class="text-[11px] mt-1 truncate" :class="activeTab === 'Digital A' ? 'text-emerald-100' : 'text-slate-500'">Pulsa, Data & Game</div>
        </div>

        <!-- 5. Digital B -->
        @php $cntDigitalB = $barangs->where('tipe_barang', 'Digital B')->count(); @endphp
        <div @click="setTab('Digital B')" 
             class="cursor-pointer p-4 rounded-2xl border transition-all duration-200 col-span-2 sm:col-span-1"
             :class="activeTab === 'Digital B' ? 'bg-purple-600 text-white border-purple-600 shadow-md shadow-purple-600/20' : 'bg-white hover:bg-slate-50 border-slate-200/80 text-slate-800 shadow-2xs'">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider" :class="activeTab === 'Digital B' ? 'text-purple-200' : 'text-slate-400'">Digital B</span>
                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-xs" :class="activeTab === 'Digital B' ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-600'"><i class="fa-solid fa-money-bill-transfer"></i></span>
            </div>
            <div class="text-2xl font-black" :class="activeTab === 'Digital B' ? 'text-white' : 'text-slate-900'">{{ $cntDigitalB }}</div>
            <div class="text-[11px] mt-1 truncate" :class="activeTab === 'Digital B' ? 'text-purple-100' : 'text-slate-500'">Tarik Tunai & Fee</div>
        </div>
    </div>

    <!-- Main Table Container dengan Tab Terpisah -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        
        <!-- Tab Navigation Bar -->
        <div class="border-b border-slate-200/80 px-4 pt-3 flex items-center justify-between gap-4 overflow-x-auto bg-slate-50/50">
            <div class="flex items-center gap-1.5 shrink-0">
                <button @click="setTab('Semua')" 
                        :class="activeTab === 'Semua' ? 'border-indigo-600 text-indigo-600 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-3.5 py-2.5 border-b-2 rounded-t-xl text-xs sm:text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-layer-group text-xs"></i>
                    <span>Semua Produk</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" :class="activeTab === 'Semua' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-200 text-slate-600'">{{ $barangs->count() }}</span>
                </button>

                <button @click="setTab('Fisik ACC')" 
                        :class="activeTab === 'Fisik ACC' ? 'border-amber-600 text-amber-700 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-3.5 py-2.5 border-b-2 rounded-t-xl text-xs sm:text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-plug text-xs"></i>
                    <span>Fisik ACC (Aksesoris)</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" :class="activeTab === 'Fisik ACC' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600'">{{ $cntFisikAcc }}</span>
                </button>

                <button @click="setTab('Fisik Voucher Kartu')" 
                        :class="activeTab === 'Fisik Voucher Kartu' ? 'border-blue-600 text-blue-700 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-3.5 py-2.5 border-b-2 rounded-t-xl text-xs sm:text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-sim-card text-xs"></i>
                    <span>Fisik Voucher & Kartu</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" :class="activeTab === 'Fisik Voucher Kartu' ? 'bg-blue-100 text-blue-800' : 'bg-slate-200 text-slate-600'">{{ $cntFisikVoucher }}</span>
                </button>

                <button @click="setTab('Digital A')" 
                        :class="activeTab === 'Digital A' ? 'border-emerald-600 text-emerald-700 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-3.5 py-2.5 border-b-2 rounded-t-xl text-xs sm:text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-bolt text-xs"></i>
                    <span>Digital A (Pulsa & Data)</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" :class="activeTab === 'Digital A' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">{{ $cntDigitalA }}</span>
                </button>

                <button @click="setTab('Digital B')" 
                        :class="activeTab === 'Digital B' ? 'border-purple-600 text-purple-700 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-3.5 py-2.5 border-b-2 rounded-t-xl text-xs sm:text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-money-bill-transfer text-xs"></i>
                    <span>Digital B (Tarik Tunai & Fee)</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-mono" :class="activeTab === 'Digital B' ? 'bg-purple-100 text-purple-800' : 'bg-slate-200 text-slate-600'">{{ $cntDigitalB }}</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="p-4 border-b border-slate-200/80 bg-white flex flex-col md:flex-row justify-between items-center gap-3">
            <!-- Search Input -->
            <div class="w-full md:w-72 relative">
                <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3.5 top-3 text-xs"></i>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Cari ID atau nama barang..." 
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>

            <!-- Filter Kategori & Sort -->
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-end">
                <!-- Dropdown Kategori Induk/Sub -->
                <select x-model="filterKategori" 
                        class="border border-slate-200 bg-slate-50 rounded-xl py-2 px-3 text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">Semua Kategori</option>
                    <template x-for="cat in filteredKategoriSelect" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>

                <!-- Dropdown Urutkan -->
                <select x-model="sortOrder" @change="sortTabel()" 
                        class="border border-slate-200 bg-slate-50 rounded-xl py-2 px-3 text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <option value="id_desc">ID (Terbaru)</option>
                    <option value="id_asc">ID (Terlama)</option>
                    <option value="nama_asc">Nama (A-Z)</option>
                    <option value="kategori">Kategori</option>
                    <option value="harga_desc">Harga Tertinggi</option>
                    <option value="harga_asc">Harga Terendah</option>
                </select>

                <!-- Cetak / Ekspor Ringan -->
                <a href="{{ route('master.barang.cetak') }}" target="_blank" class="p-2 border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition text-xs flex items-center gap-1.5 shadow-2xs" title="Cetak Data Katalog">
                    <i class="fa-solid fa-print"></i>
                    <span class="hidden sm:inline">Cetak</span>
                </a>
            </div>
        </div>
        
        <!-- TABEL DATA MASTER (RESPONSIF) -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs sm:text-sm">
                <!-- Header Spesifik per Tab -->
                <thead class="bg-slate-50/80 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-4 py-3.5 text-left w-16">ID</th>
                        <th class="px-4 py-3.5 text-left">Nama Produk / Layanan</th>
                        <th class="px-4 py-3.5 text-left">Kategori Induk ➔ Sub</th>
                        
                        <!-- Kolom Tipe Barang (Hanya jika di Tab Semua) -->
                        <th x-show="activeTab === 'Semua'" class="px-4 py-3.5 text-left">Tipe Produk</th>
                        
                        <!-- Kolom Khusus Fisik: Safety Stock Buffer -->
                        <th x-show="activeTab === 'Fisik ACC' || activeTab === 'Fisik Voucher Kartu' || activeTab === 'Semua'" class="px-4 py-3.5 text-center">Safety Stock (AI)</th>
                        
                        <!-- Kolom Khusus Digital B: Jenis Arus Kas -->
                        <th x-show="activeTab === 'Digital B'" class="px-4 py-3.5 text-left">Arus Kas Kasir</th>
                        
                        <!-- Kolom Harga Jual / Fee -->
                        <th class="px-4 py-3.5 text-left">
                            <span x-show="activeTab !== 'Digital B'">Harga Jual Cabang</span>
                            <span x-show="activeTab === 'Digital B'">Skema Range Fee Admin</span>
                        </th>
                        
                        <th class="px-4 py-3.5 text-right w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="barangTableBody">
                    @forelse($barangs as $barang)
                    <tr class="hover:bg-slate-50/80 transition barang-row" 
                        data-id="{{ $barang->id }}" 
                        data-tipe="{{ htmlspecialchars(strtolower($barang->tipe_barang), ENT_QUOTES) }}"
                        data-nama="{{ htmlspecialchars(strtolower($barang->nama_barang), ENT_QUOTES) }}" 
                        data-kategori="{{ htmlspecialchars(strtolower($barang->category ? ($barang->category->parent ? $barang->category->parent->name . ' ' . $barang->category->name : $barang->category->name) : 'zzz'), ENT_QUOTES) }}"
                        data-harga="{{ $barang->harga_jual ?? 0 }}"
                        x-show="(activeTab === 'Semua' || activeTab === '{{ $barang->tipe_barang }}') && (filterKategori === '' || '{{ $barang->category_id }}' === filterKategori || '{{ $barang->category ? $barang->category->parent_id : '' }}' === filterKategori) && ('{{ strtolower(addslashes($barang->nama_barang)) }}'.includes(searchQuery.toLowerCase()) || '{{ $barang->id }}'.includes(searchQuery))">
                        
                        <!-- ID -->
                        <td class="px-4 py-3.5 text-slate-400 font-mono font-bold text-xs">
                            #{{ $barang->id }}
                        </td>

                        <!-- Nama Produk -->
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-800">{{ $barang->nama_barang }}</div>
                            @if($barang->tipe_barang === 'Digital B' && $barang->jenis_transaksi)
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-500">
                                    <i class="fa-solid fa-arrows-spin text-[9px]"></i>
                                    {{ $barang->jenis_transaksi === 'cash_in' ? 'Cash In (Uang Masuk Laci)' : 'Cash Out (Uang Keluar Laci)' }}
                                </span>
                            @endif
                        </td>

                        <!-- Kategori Induk ➔ Sub -->
                        <td class="px-4 py-3.5 text-slate-600">
                            @if($barang->category)
                                @if($barang->category->parent)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[11px] font-medium text-slate-400">{{ $barang->category->parent->name }}</span>
                                        <i class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                                        <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">{{ $barang->category->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">{{ $barang->category->name }}</span>
                                @endif
                            @else
                                <span class="text-xs italic text-slate-400">Tanpa Kategori</span>
                            @endif
                        </td>

                        <!-- Tipe Produk (Khusus Tab Semua) -->
                        <td x-show="activeTab === 'Semua'" class="px-4 py-3.5">
                            @if($barang->tipe_barang === 'Fisik ACC')
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-plug text-[9px]"></i> Fisik ACC
                                </span>
                            @elseif($barang->tipe_barang === 'Fisik Voucher Kartu')
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-sim-card text-[9px]"></i> Fisik Voucher
                                </span>
                            @elseif($barang->tipe_barang === 'Digital A')
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-bolt text-[9px]"></i> Digital A
                                </span>
                            @elseif($barang->tipe_barang === 'Digital B')
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-700 bg-purple-50 border border-purple-200 px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-money-bill-transfer text-[9px]"></i> Digital B
                                </span>
                            @else
                                <span class="text-xs text-slate-500">{{ $barang->tipe_barang }}</span>
                            @endif
                        </td>

                        <!-- Safety Stock Buffer (Khusus Fisik ACC / Voucher) -->
                        <td x-show="activeTab === 'Fisik ACC' || activeTab === 'Fisik Voucher Kartu' || activeTab === 'Semua'" class="px-4 py-3.5 text-center">
                            @if($barang->tipe_barang === 'Fisik ACC' || $barang->tipe_barang === 'Fisik Voucher Kartu')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-orange-700 bg-orange-50 border border-orange-200 px-2.5 py-0.5 rounded-full font-mono">
                                    <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                    {{ $barang->safety_stock_percent ?? 20 }}% Buffer
                                </span>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>

                        <!-- Jenis Arus Kas (Khusus Tab Digital B) -->
                        <td x-show="activeTab === 'Digital B'" class="px-4 py-3.5">
                            @if($barang->jenis_transaksi === 'cash_in')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                                    <i class="fa-solid fa-arrow-down-long text-[10px]"></i>
                                    Cash In (Masuk Kas)
                                </span>
                            @elseif($barang->jenis_transaksi === 'cash_out')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded-full">
                                    <i class="fa-solid fa-arrow-up-long text-[10px]"></i>
                                    Cash Out (Keluar Kas)
                                </span>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum diatur</span>
                            @endif
                        </td>

                        <!-- Harga Jual / Range Fee Admin -->
                        <td class="px-4 py-3.5">
                            @if($barang->tipe_barang === 'Digital B')
                                @if($barang->rangesDigitalB && $barang->rangesDigitalB->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 max-w-sm">
                                        @foreach($barang->rangesDigitalB as $range)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-50 text-purple-800 border border-purple-200 rounded text-[11px] font-mono">
                                                <span>Rp {{ number_format($range->min_nominal, 0, ',', '.') }}-{{ number_format($range->max_nominal, 0, ',', '.') }}:</span>
                                                <b class="text-purple-900">Rp {{ number_format($range->fee, 0, ',', '.') }}</b>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-purple-600 italic bg-purple-50 px-2 py-0.5 rounded border border-purple-100">Range Fee Belum Diset</span>
                                @endif
                            @else
                                <div class="font-extrabold text-slate-800 text-sm">
                                    Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}
                                </div>
                            @endif
                        </td>

                        <!-- Tombol Aksi -->
                        <td class="px-4 py-3.5 text-right font-medium">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="editBarang({{ $barang->toJson() }})" 
                                        class="p-2 rounded-lg text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 border border-transparent hover:border-indigo-200 transition" 
                                        title="Edit Item Master">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button @click="deleteBarang({{ $barang->id }}, '{{ $barang->tipe_barang }}')" 
                                        class="p-2 rounded-lg text-rose-600 hover:text-rose-800 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition" 
                                        title="Hapus Item">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div class="font-bold text-slate-600 text-sm">Belum ada data barang terdaftar.</div>
                            <p class="text-xs text-slate-400 mt-1">Klik tombol "+ Tambah Item Baru" untuk mulai memasukkan katalog.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Info -->
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
            <div>
                Menampilkan seluruh barang master di sistem pusat. Setiap perubahan langsung tersinkron ke modul POS dan EWS.
            </div>
            <div class="font-semibold text-slate-700">
                Total: {{ $barangs->count() }} Item Terdaftar
            </div>
        </div>
    </div>

    <!-- Modal Form Tambah / Edit Master Item -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-3 py-6 text-center sm:p-0">
            
            <div x-show="modalOpen" 
                 x-transition.opacity 
                 @click="modalOpen = false" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

            <div x-show="modalOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl sm:my-8 sm:align-middle sm:max-w-2xl w-full relative z-50 border border-slate-100">
                
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-blue-700 px-6 py-4 flex justify-between items-center text-white">
                    <div>
                        <h3 class="text-base sm:text-lg font-black tracking-tight" x-text="editBarangId ? 'Edit Master Item' : 'Tambah Master Item Baru'"></h3>
                        <p class="text-xs text-indigo-100 mt-0.5">Atur tipe, kategori hierarkis, harga jual, dan skema biaya</p>
                    </div>
                    <button @click="modalOpen = false" class="p-1.5 rounded-lg text-indigo-200 hover:text-white hover:bg-white/10 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="editBarangId ? `/master/barang/${editBarangId}` : '{{ route('master.barang.store') }}'" method="POST" @submit="checkSafetyStock($event)">
                    @csrf
                    <template x-if="editBarangId">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <div class="p-5 sm:p-6 bg-slate-50/50 space-y-4 max-h-[75vh] overflow-y-auto">
                        
                        <!-- 1. Tipe Barang Utama -->
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                            <label class="flex items-center justify-between text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-shapes text-indigo-600"></i>
                                    Tipe Produk / Logika Bisnis <span class="text-red-500">*</span>
                                </span>
                                <button type="button" @click="showInfoDigital()" class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold inline-flex items-center gap-1">
                                    <i class="fa-solid fa-circle-question"></i>
                                    <span>Panduan Tipe</span>
                                </button>
                            </label>
                            <select x-model="tipeBarang" name="tipe_barang" required class="w-full border border-slate-200 rounded-xl p-3 bg-slate-50 text-slate-800 text-xs sm:text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">-- Pilih Tipe Produk --</option>
                                <option value="Fisik ACC">Fisik ACC (Aksesoris HP / Fisik Stok)</option>
                                <option value="Fisik Voucher Kartu">Fisik Voucher Kartu (Voucher Fisik / Perdana)</option>
                                <option value="Digital A">Digital A (Pulsa, Kuota Elektrik, Game)</option>
                                <option value="Digital B">Digital B (Tarik Tunai, Transfer Bank / Range Fee)</option>
                            </select>
                        </div>

                        <!-- 2. Form Detil Produk Sesuai Tipe -->
                        <div x-show="tipeBarang" class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-2xs space-y-4">
                            <input type="hidden" name="category_id" :value="finalCategoryId">
                            
                            <!-- Chained Dropdowns Kategori Induk -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Kategori Induk <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                                </label>
                                <select x-model="selectedParentId" class="w-full border border-slate-200 rounded-xl p-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20" @change="resetSub()">
                                    <option value="">-- Pilih Kategori Induk --</option>
                                    <template x-for="cat in filteredCategoriesTree" :key="cat.id">
                                        <option :value="cat.id" x-text="cat.name"></option>
                                    </template>
                                </select>
                                <p x-show="filteredCategoriesTree.length === 0" class="text-[11px] text-slate-400 mt-1">Belum ada Kategori untuk Tipe Barang ini.</p>
                            </div>

                            <!-- Sub Kategori -->
                            <div x-show="availableSub1.length > 0">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sub Kategori</label>
                                <select x-model="selectedSub1Id" class="w-full border border-slate-200 rounded-xl p-2.5 bg-slate-50 text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                    <option value="">-- Pilih Sub Kategori --</option>
                                    <template x-for="cat in availableSub1" :key="cat.id">
                                        <option :value="cat.id" x-text="cat.name"></option>
                                    </template>
                                </select>
                            </div>
                            
                            <!-- Nama Produk -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nama Produk / Layanan <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nama_barang" x-model="namaBarang" required placeholder="Contoh: Kabel Data Fast Charging Type-C" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                            </div>

                            <!-- Arus Kasir (Khusus Digital B) -->
                            <div x-show="tipeBarang === 'Digital B'" class="p-3.5 bg-purple-50/70 border border-purple-200 rounded-xl">
                                <label class="block text-xs font-bold text-purple-900 uppercase tracking-wider mb-2">
                                    Jenis Transaksi Kasir <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-lg border border-purple-200 cursor-pointer hover:bg-purple-50/50 transition">
                                        <input type="radio" name="jenis_transaksi" value="cash_in" x-model="jenisTransaksi" class="text-purple-600 focus:ring-purple-500">
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">Cash In (Top Up)</div>
                                            <div class="text-[10px] text-slate-500">Uang tunai masuk laci</div>
                                        </div>
                                    </label>
                                    <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-lg border border-purple-200 cursor-pointer hover:bg-purple-50/50 transition">
                                        <input type="radio" name="jenis_transaksi" value="cash_out" x-model="jenisTransaksi" class="text-purple-600 focus:ring-purple-500">
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">Cash Out (Tarik Tunai)</div>
                                            <div class="text-[10px] text-slate-500">Uang tunai keluar laci</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Harga Jual (Untuk Non-Digital B) -->
                            <div x-show="tipeBarang !== 'Digital B'">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Harga Jual Cabang (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-2.5 text-xs font-bold text-slate-400">Rp</span>
                                    <input type="number" name="harga_jual" x-model="hargaJual" :required="tipeBarang !== 'Digital B'" class="w-full border border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-sm font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20" placeholder="0">
                                </div>
                            </div>

                            <!-- Safety Stock (%) Khusus Barang Fisik -->
                            <div x-show="tipeBarang === 'Fisik ACC' || tipeBarang === 'Fisik Voucher Kartu'" class="p-3.5 bg-amber-50/70 border border-amber-200 rounded-xl space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-amber-900 uppercase tracking-wider">
                                        Safety Stock Buffer (%)
                                    </label>
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded">Rekomendasi AI: 20%</span>
                                </div>
                                <input type="number" name="safety_stock_percent" x-model="safetyStockPercent" min="0" max="100" class="w-full border border-amber-300 rounded-xl p-2.5 text-sm font-bold text-amber-900 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20" placeholder="20">
                                <p class="text-[11px] text-amber-800 leading-relaxed">
                                    Menentukan batas peringatan stok menipis di EWS Cabang (Stok Kritis). Default ideal adalah 20%.
                                </p>
                            </div>

                            <!-- Skema Range Fee Admin (Khusus Digital B) -->
                            <div x-show="tipeBarang === 'Digital B'" class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Skema Biaya Admin (Range Fee)</label>
                                    <button type="button" @click="addRange()" class="bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold py-1 px-3 rounded-lg text-xs transition inline-flex items-center gap-1 shadow-2xs">
                                        <i class="fa-solid fa-plus text-[10px]"></i>
                                        <span>Tambah Tingkatan Fee</span>
                                    </button>
                                </div>
                                <input type="hidden" name="ranges" x-model="rangesJson">
                                <div class="space-y-2.5">
                                    <template x-for="(range, index) in ranges" :key="index">
                                        <div class="flex items-center gap-2 bg-slate-50 p-2.5 border border-slate-200 rounded-xl">
                                            <input type="number" x-model="range.min" class="w-1/3 border border-slate-200 rounded-lg p-2 text-xs" placeholder="Min Nominal" :required="tipeBarang === 'Digital B'">
                                            <span class="text-slate-400 text-xs">-</span>
                                            <input type="number" x-model="range.max" class="w-1/3 border border-slate-200 rounded-lg p-2 text-xs" placeholder="Max Nominal" :required="tipeBarang === 'Digital B'">
                                            <input type="number" x-model="range.fee" class="w-1/3 border border-purple-300 rounded-lg p-2 text-xs font-bold text-purple-700" placeholder="Biaya Fee (Rp)" :required="tipeBarang === 'Digital B'">
                                            <button type="button" @click="removeRange(index)" class="text-rose-500 hover:text-rose-700 p-1.5 rounded-lg hover:bg-rose-50 transition">
                                                <i class="fa-solid fa-xmark text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-2.5 border-t border-slate-200/80">
                        <button type="submit" x-bind:disabled="!tipeBarang || (tipeBarang === 'Digital B' && !jenisTransaksi)" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl shadow-md px-6 py-2.5 bg-indigo-600 text-white font-bold hover:bg-indigo-700 disabled:opacity-50 transition text-xs sm:text-sm">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Master Item</span>
                        </button>
                        <button type="button" @click="modalOpen = false" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl px-5 py-2.5 bg-white text-slate-700 font-bold border border-slate-200 hover:bg-slate-100 transition text-xs sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL KELOLA KATEGORI INDUK -->
    <div x-show="kategoriModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-3 py-6 text-center sm:p-0">
            <div x-show="kategoriModalOpen" x-transition.opacity @click="kategoriModalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>
            
            <div x-show="kategoriModalOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50 border border-slate-100">
                
                <div class="bg-teal-600 px-6 py-4 flex justify-between items-center text-white">
                    <div>
                        <h3 class="text-base sm:text-lg font-black tracking-tight">Kelola Kategori Induk</h3>
                        <p class="text-xs text-teal-100 mt-0.5">Struktur kategori hirarkis untuk katalog barang</p>
                    </div>
                    <button @click="kategoriModalOpen = false" class="p-1.5 rounded-lg text-teal-200 hover:text-white hover:bg-white/10 transition">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('master.kategori.store_induk') }}" method="POST">
                    @csrf
                    <div class="p-5 sm:p-6 bg-slate-50/50 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Untuk Tipe Barang <span class="text-red-500">*</span></label>
                            <select name="tipe_barang_terkait" x-model="kategoriTipeBarang" required class="w-full border border-slate-200 rounded-xl p-2.5 bg-white text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                                <option value="">-- Pilih Tipe --</option>
                                <option value="Fisik ACC">Fisik ACC</option>
                                <option value="Fisik Voucher Kartu">Fisik Voucher Kartu</option>
                                <option value="Digital A">Digital A (Pulsa/Data)</option>
                                <option value="Digital B">Digital B (PPOB/Tarik Tunai)</option>
                            </select>
                        </div>

                        <div x-show="kategoriTipeBarang">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Induk Kategori (Opsional)</label>
                            <select name="parent_id" class="w-full border border-slate-200 rounded-xl p-2.5 bg-white text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                                <option value="">-- Buat sebagai Kategori Utama (Level 1) --</option>
                                <template x-for="cat in filteredKategoriSelect" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.name"></option>
                                </template>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Jika memilih induk, kategori baru akan menjadi sub-kategori.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Kategori Baru <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Aksesoris Charger / Provider Telkomsel" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs sm:text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/20">
                        </div>
                    </div>
                    
                    <div class="bg-slate-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-2.5 border-t border-slate-200/80">
                        <button type="submit" x-bind:disabled="!kategoriTipeBarang" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl shadow-md px-5 py-2.5 bg-teal-600 text-white font-bold hover:bg-teal-700 transition disabled:opacity-50 text-xs sm:text-sm">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Simpan Kategori</span>
                        </button>
                        <button type="button" @click="kategoriModalOpen = false" class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl px-4 py-2.5 bg-white text-slate-700 font-bold border border-slate-200 hover:bg-slate-100 transition text-xs sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
                
                <!-- Daftar Kategori Tersimpan -->
                <div class="p-5 border-t border-slate-200 bg-white" x-show="kategoriTipeBarang">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <i class="fa-solid fa-list-tree text-teal-600"></i>
                        <span>Daftar Kategori yang Sudah Ada</span>
                    </h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <template x-for="cat in filteredKategoriUtama" :key="cat.id">
                            <div class="space-y-1.5">
                                <!-- Parent Category -->
                                <div class="flex justify-between items-center bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                                    <div class="font-bold text-xs text-slate-800" x-text="cat.name"></div>
                                    <div class="flex items-center gap-1">
                                        <button @click="editKategori(cat.id, cat.name)" class="text-[11px] bg-amber-100 hover:bg-amber-200 text-amber-800 px-2 py-0.5 rounded font-bold transition">Edit</button>
                                        <button @click="deleteKategori(cat.id)" class="text-[11px] bg-rose-100 hover:bg-rose-200 text-rose-800 px-2 py-0.5 rounded font-bold transition">Hapus</button>
                                    </div>
                                </div>
                                
                                <!-- Children Categories -->
                                <div class="pl-4 space-y-1" x-show="cat.children && cat.children.length > 0">
                                    <template x-for="child in cat.children" :key="child.id">
                                        <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-l-4 border-l-teal-500 text-xs">
                                            <div class="text-slate-600 font-medium" x-text="child.name"></div>
                                            <div class="flex items-center gap-2">
                                                <button @click="editKategori(child.id, child.name)" class="text-amber-600 hover:underline text-[11px] font-bold">Edit</button>
                                                <button @click="deleteKategori(child.id)" class="text-rose-600 hover:underline text-[11px] font-bold">Hapus</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredKategoriUtama.length === 0" class="text-xs text-slate-400 text-center py-3">
                            Belum ada kategori untuk tipe ini.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Hidden form for Edit & Delete Kategori -->
    <form id="editKategoriForm" method="POST" style="display: none;">
        @csrf
        @method('PUT')
        <input type="hidden" name="name" id="editKategoriName">
    </form>
    <form id="deleteKategoriForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    <form id="deleteBarangForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
    function masterBarangForm(categoriesData) {
        return {
            activeTab: 'Semua', // 'Semua', 'Fisik ACC', 'Fisik Voucher Kartu', 'Digital A', 'Digital B'
            filterKategori: '',
            searchQuery: '',
            sortOrder: 'id_desc',
            categoriesTree: categoriesData,
            modalOpen: {{ session('keep_modal_open') === 'barang' ? 'true' : 'false' }},
            kategoriModalOpen: {{ session('keep_modal_open') === 'kategori' ? 'true' : 'false' }},
            
            setTab(tabName) {
                this.activeTab = tabName;
                this.filterKategori = '';
                // Optional: sync sort
                this.sortTabel();
            },

            sortTabel() {
                setTimeout(() => {
                    const tbody = document.getElementById('barangTableBody');
                    if (!tbody) return;
                    const rows = Array.from(tbody.querySelectorAll('tr.barang-row'));
                    const emptyRow = tbody.querySelector('tr:not(.barang-row)');
                    
                    rows.sort((a, b) => {
                        if (this.sortOrder === 'nama_asc') {
                            return a.dataset.nama.localeCompare(b.dataset.nama);
                        } else if (this.sortOrder === 'kategori') {
                            return a.dataset.kategori.localeCompare(b.dataset.kategori) || a.dataset.nama.localeCompare(b.dataset.nama);
                        } else if (this.sortOrder === 'id_asc') {
                            return parseInt(a.dataset.id) - parseInt(b.dataset.id);
                        } else if (this.sortOrder === 'harga_desc') {
                            return (parseFloat(b.dataset.harga) || 0) - (parseFloat(a.dataset.harga) || 0);
                        } else if (this.sortOrder === 'harga_asc') {
                            return (parseFloat(a.dataset.harga) || 0) - (parseFloat(b.dataset.harga) || 0);
                        } else {
                            return parseInt(b.dataset.id) - parseInt(a.dataset.id);
                        }
                    });
                    
                    rows.forEach(row => tbody.appendChild(row));
                    if (emptyRow) tbody.appendChild(emptyRow);
                }, 50);
            },
            
            editBarangId: null,
            tipeBarang: '{{ session('keep_modal_open') === 'barang' ? session('last_tipe_barang', '') : '' }}',
            jenisTransaksi: '',
            namaBarang: '',
            hargaJual: '',
            safetyStockPercent: 20,
            
            selectedParentId: '',
            selectedSub1Id: '',
            
            kategoriTipeBarang: '{{ session('last_tipe_barang_kategori', '') }}',

            ranges: [{ min: '', max: '', fee: '' }],
            rangesJson: '',
            
            get filteredCategoriesTree() {
                if (!this.tipeBarang) return [];
                return this.categoriesTree.filter(c => c.tipe_barang_terkait === this.tipeBarang);
            },

            get filteredKategoriSelect() {
                // Untuk filter tampilan tabel sesuai kategori yang terkait dengan tab aktif
                const targetTipe = this.activeTab === 'Semua' ? '' : this.activeTab;
                let options = [];
                let pool = targetTipe ? this.categoriesTree.filter(c => c.tipe_barang_terkait === targetTipe) : this.categoriesTree;
                
                pool.forEach(cat => {
                    options.push({ id: cat.id, name: cat.name });
                    if(cat.children) {
                        cat.children.forEach(sub => {
                            options.push({ id: sub.id, name: '— ' + sub.name });
                        });
                    }
                });
                return options;
            },

            get filteredKategoriUtama() {
                if (!this.kategoriTipeBarang) return [];
                return this.categoriesTree.filter(c => c.tipe_barang_terkait === this.kategoriTipeBarang && c.parent_id === null);
            },

            get availableSub1() {
                if (!this.selectedParentId) return [];
                const parent = this.filteredCategoriesTree.find(c => c.id == this.selectedParentId);
                return parent && parent.children ? parent.children : [];
            },
            
            get finalCategoryId() {
                if (this.selectedSub1Id) return this.selectedSub1Id;
                return this.selectedParentId;
            },
            
            resetSub() {
                this.selectedSub1Id = '';
            },
            
            addRange() {
                this.ranges.push({ min: '', max: '', fee: '' });
            },
            
            removeRange(index) {
                this.ranges.splice(index, 1);
            },

            editKategori(id, currentName) {
                const newName = prompt('Ubah Nama Kategori:', currentName);
                if (newName && newName !== currentName) {
                    const form = document.getElementById('editKategoriForm');
                    form.action = `/master/kategori/${id}`;
                    document.getElementById('editKategoriName').value = newName;
                    form.submit();
                }
            },

            deleteKategori(id) {
                if (confirm('Yakin ingin menghapus kategori ini? Kategori tidak bisa dihapus jika sedang digunakan.')) {
                    const form = document.getElementById('deleteKategoriForm');
                    form.action = `/master/kategori/${id}`;
                    form.submit();
                }
            },

            deleteBarang(id, tipe) {
                let msg = '';
                if (tipe === 'Fisik ACC' || tipe === 'Fisik Voucher Kartu') {
                    msg = 'PERHATIAN!\n\nMenghapus master barang fisik ini akan mempengaruhi pencatatan STOK dan riwayat mutasi di cabang!\nPastikan barang ini memang belum pernah digunakan atau stoknya sudah 0 di mana-mana.\n\nApakah Anda yakin ingin menghapus master barang ini?';
                } else {
                    msg = 'Yakin ingin menghapus produk digital ini? Jika sudah pernah ada transaksi, penghapusan mungkin akan ditolak oleh sistem.';
                }

                if (confirm(msg)) {
                    const form = document.getElementById('deleteBarangForm');
                    form.action = `/master/barang/${id}`;
                    form.submit();
                }
            },

            checkSafetyStock(e) {
                if (this.tipeBarang === 'Fisik ACC' || this.tipeBarang === 'Fisik Voucher Kartu') {
                    let val = parseInt(this.safetyStockPercent);
                    if (val <= 5) {
                        if (!confirm(`Anda mengatur Safety Stock di angka ${val}%. Angka 5% ke bawah berisiko menyebabkan kekosongan stok saat terjadi lonjakan pembeli. Apakah Anda yakin ingin melanjutkannya?`)) {
                            e.preventDefault();
                            return false;
                        }
                    } else if (val >= 50) {
                        if (!confirm(`Anda mengatur Safety Stock di angka ${val}%. Angka 50% ke atas berisiko menyebabkan penumpukan stok mati di cabang. Apakah Anda yakin ingin melanjutkannya?`)) {
                            e.preventDefault();
                            return false;
                        }
                    }
                }
            },

            openTambahModal() {
                this.editBarangId = null;
                // Pre-select tipeBarang based on current activeTab if specific
                if (['Fisik ACC', 'Fisik Voucher Kartu', 'Digital A', 'Digital B'].includes(this.activeTab)) {
                    this.tipeBarang = this.activeTab;
                }
                this.jenisTransaksi = '';
                this.namaBarang = '';
                this.hargaJual = '';
                this.safetyStockPercent = 20;
                this.selectedParentId = '';
                this.selectedSub1Id = '';
                this.ranges = [{ min: '', max: '', fee: '' }];
                this.modalOpen = true;
            },

            editBarang(barang) {
                this.editBarangId = barang.id;
                this.tipeBarang = barang.tipe_barang;
                this.jenisTransaksi = barang.jenis_transaksi || '';
                this.namaBarang = barang.nama_barang;
                this.hargaJual = barang.harga_jual;
                this.safetyStockPercent = barang.safety_stock_percent !== null ? barang.safety_stock_percent : 20;
                
                this.selectedParentId = '';
                this.selectedSub1Id = '';
                
                if (barang.category) {
                    if (barang.category.parent_id === null) {
                        this.selectedParentId = barang.category.id;
                    } else {
                        const parent = barang.category.parent;
                        if (parent && parent.parent_id === null) {
                            this.selectedParentId = parent.id;
                            this.$nextTick(() => {
                                this.selectedSub1Id = barang.category.id;
                            });
                        } else if (parent && parent.parent) {
                            this.selectedParentId = parent.parent.id;
                            this.$nextTick(() => {
                                this.selectedSub1Id = parent.id;
                            });
                        }
                    }
                }

                if (barang.tipe_barang === 'Digital B' && barang.ranges_digital_b && barang.ranges_digital_b.length > 0) {
                    this.ranges = barang.ranges_digital_b.map(r => ({
                        min: r.min_nominal,
                        max: r.max_nominal,
                        fee: r.fee
                    }));
                } else {
                    this.ranges = [{ min: '', max: '', fee: '' }];
                }
                
                this.modalOpen = true;
            },

            showInfoDigital() {
                Swal.fire({
                    title: 'Panduan Tipe Produk & Logika',
                    html: `
                        <div class="text-left text-xs sm:text-sm space-y-3 leading-relaxed">
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                                <b class="text-amber-900 block">🔌 Fisik ACC (Aksesoris HP):</b>
                                <span class="text-amber-800">Barang fisik seperti Charger, Case, Tempered Glass, Kabel Data. Memiliki stok fisik nyata di cabang & dipantau oleh Buffer EWS.</span>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200">
                                <b class="text-blue-900 block">🎫 Fisik Voucher & Kartu Perdana:</b>
                                <span class="text-blue-800">Voucher kuota fisik dan kartu perdana. Menggunakan stok fisik rak dan memiliki batas kritis restok.</span>
                            </div>
                            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                                <b class="text-emerald-900 block">⚡ Digital A (Pulsa & Paket Data):</b>
                                <span class="text-emerald-800">Transaksi elektrik (isi pulsa, paket data, token PLN, top up game). Tidak menggunakan stok fisik gudang, melainkan memotong saldo rekening server.</span>
                            </div>
                            <div class="p-3 bg-purple-50 rounded-xl border border-purple-200">
                                <b class="text-purple-900 block">💳 Digital B (Tarik Tunai & Transfer):</b>
                                <span class="text-purple-800">Layanan keuangan perbankan kasir. Melibatkan arus uang tunai laci (Cash In / Cash Out) dan skema rentang biaya admin (Range Fee).</span>
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Saya Paham'
                });
            }
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#4f46e5',
                confirmButtonText: 'OK'
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tutup'
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                html: `
                    <ul class="text-left list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                `,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Tutup'
            });
        @endif
    });
</script>
@endpush
@endsection
