@extends('layouts.app')
@section('title', 'Dashboard Kasir')

@push('styles')
<style>
    /* Styling khusus struk thermal modal */
    .receipt-paper {
        background: #ffffff;
        background-image: radial-gradient(#f1f5f9 1px, transparent 0);
        background-size: 8px 8px;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="riwayatTransaksiData()">

    <!-- 1. HERO GREETING & STATUS SHIFT BANNER -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 via-indigo-700 to-violet-800 text-white p-6 sm:p-8 shadow-xl">
        <!-- Background decorative glow -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute right-12 top-6 opacity-10 hidden lg:block pointer-events-none">
            <i class="fa-solid fa-cash-register text-9xl"></i>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/20 flex items-center gap-1.5 shadow-2xs">
                        <i class="fa-solid fa-store text-[11px] text-amber-300"></i>
                        <span>{{ $cabang }}</span>
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 flex items-center gap-1.5 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Shift Kasir Aktif</span>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                    Selamat Bertugas, {{ Auth::user()->name }}! 👋
                </h1>
                <p class="text-xs sm:text-sm text-indigo-100 max-w-xl leading-relaxed">
                    Pantau arus kas laci, konfirmasi mutasi masuk dari gudang pusat, dan mulai layani transaksi pelanggan melalui tombol cepat di bawah.
                </p>
            </div>

            <!-- Primary Action Buttons -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto shrink-0">
                <a href="{{ route('kasir.pos') }}" class="group inline-flex items-center justify-center gap-3 px-6 py-3.5 bg-white text-indigo-700 hover:bg-indigo-50 font-black rounded-2xl shadow-lg shadow-black/10 transition transform hover:-translate-y-0.5 text-sm sm:text-base">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center group-hover:scale-110 transition">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <span>Buka Layar Kasir</span>
                    <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                </a>
                <a href="{{ route('kasir.closing.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-3.5 bg-rose-500/20 hover:bg-rose-500/30 text-white font-bold rounded-2xl border border-rose-300/30 backdrop-blur-sm transition text-xs sm:text-sm">
                    <i class="fa-solid fa-lock text-rose-300"></i>
                    <span>Tutup Shift</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. METRIK KEUANGAN & OPERASIONAL SHIFT HARI INI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Saldo Kas Laci Cabang -->
        <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kas Laci Cabang</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">
                Rp {{ number_format($saldoKasLaci ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-money-bill-wave text-emerald-500"></i>
                <span>Saldo uang fisik saat ini di laci kasir</span>
            </div>
        </div>

        <!-- 2. Omset Penjualan Hari Ini -->
        <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Omset Hari Ini</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">
                Rp {{ number_format($omsetHariIni ?? 0, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                <i class="fa-solid fa-chart-line text-indigo-500"></i>
                <span>Total tagihan transaksi shift hari ini</span>
            </div>
        </div>

        <!-- 3. Transaksi Selesai -->
        <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Transaksi Selesai</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900 tracking-tight">
                {{ $transaksiSelesai ?? 0 }} <span class="text-xs font-bold text-slate-400 font-sans">Struk</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                <span class="font-bold text-emerald-600">{{ $transaksiTunaiCount ?? 0 }} Tunai</span>
                <span>•</span>
                <span class="font-bold text-blue-600">{{ $transaksiNonTunaiCount ?? 0 }} Non-Tunai</span>
            </div>
        </div>

        <!-- 4. Mutasi Masuk dari Pusat -->
        <div class="p-5 bg-white rounded-2xl border transition {{ isset($pendingMutasiCount) && $pendingMutasiCount > 0 ? 'border-amber-300 bg-amber-50/30 shadow-md' : 'border-slate-200/80 shadow-2xs' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider {{ isset($pendingMutasiCount) && $pendingMutasiCount > 0 ? 'text-amber-800' : 'text-slate-500' }}">Mutasi Masuk</span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ isset($pendingMutasiCount) && $pendingMutasiCount > 0 ? 'bg-amber-100 text-amber-700 animate-bounce' : 'bg-slate-100 text-slate-600' }}">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
            </div>
            <div class="text-2xl font-black tracking-tight {{ isset($pendingMutasiCount) && $pendingMutasiCount > 0 ? 'text-amber-900' : 'text-slate-900' }}">
                {{ $pendingMutasiCount ?? 0 }} <span class="text-xs font-bold text-slate-400 font-sans">Kiriman</span>
            </div>
            <div class="text-[11px] mt-1 {{ isset($pendingMutasiCount) && $pendingMutasiCount > 0 ? 'text-amber-700 font-bold' : 'text-slate-500' }}">
                {{ isset($pendingMutasiCount) && $pendingMutasiCount > 0 ? 'Menunggu konfirmasi penerimaan kasir' : 'Semua kiriman pusat telah diterima' }}
            </div>
        </div>
    </div>

    <!-- 3. ALERT BANNER: MUTASI PENDING DARI PUSAT -->
    @if(isset($pendingMutasiCount) && $pendingMutasiCount > 0)
    <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-lg flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white text-xl shrink-0">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div>
                <h3 class="text-sm sm:text-base font-extrabold tracking-tight">Ada {{ $pendingMutasiCount }} Pengiriman Barang / Uang dari Pusat!</h3>
                <p class="text-xs text-amber-100 mt-0.5">Admin gudang telah mengirimkan mutasi menuju cabang ini. Silakan verifikasi fisik dan terima mutasi.</p>
            </div>
        </div>
        <a href="{{ route('kasir.penerimaan.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white text-amber-900 hover:bg-amber-50 font-bold rounded-xl text-xs sm:text-sm shadow-md transition shrink-0">
            <span>Konfirmasi Penerimaan</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </a>
    </div>
    @endif

    <!-- 4. ALERT BANNER: EWS STOK KRITIS CABANG -->
    @if(isset($ewsKritis) && $ewsKritis->count() > 0)
    <div class="bg-white rounded-2xl border border-rose-200 shadow-2xs overflow-hidden">
        <div class="bg-rose-50 px-5 py-3.5 border-b border-rose-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-rose-500 text-white flex items-center justify-center text-xs shrink-0 animate-pulse">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div>
                    <h3 class="text-xs sm:text-sm font-black text-rose-900">
                        Peringatan EWS: {{ $ewsKritis->count() }} Produk Fisik Mendekati / Melewati Batas Kritis
                    </h3>
                    <p class="text-[11px] text-rose-700">Stok fisik barang berikut hampir habis di cabang Anda. Segera minta pengiriman restok ke pusat.</p>
                </div>
            </div>
            <a href="{{ route('kasir.request_operasional.index') }}" class="text-xs font-bold text-rose-700 hover:text-rose-900 bg-rose-100 hover:bg-rose-200 px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5 shrink-0">
                <i class="fa-solid fa-paper-plane text-[10px]"></i>
                <span>Ajukan Restok</span>
            </a>
        </div>
        <div class="p-4 overflow-x-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($ewsKritis as $stok)
                <div class="p-3 rounded-xl border border-rose-100 bg-rose-50/30 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-bold text-xs text-slate-800 truncate">{{ $stok->barang->nama_barang }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5 truncate">
                            {{ $stok->barang->category ? $stok->barang->category->name : $stok->barang->tipe_barang }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-rose-600 text-white shadow-2xs font-mono">
                            {{ $stok->jumlah_stok }} Unit
                        </span>
                        <a href="{{ route('kasir.ews.detail', $stok->barang->id) }}" class="p-1.5 text-xs rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 border border-slate-200 transition" title="Lihat Rekomendasi Restok">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- 5. MENU PINTASAN CEPAT OPERASIONAL (QUICK ACCESS) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- Shortcut 1: Buka POS -->
        <a href="{{ route('kasir.pos') }}" class="group p-4 bg-white hover:bg-indigo-50/50 rounded-2xl border border-slate-200/80 hover:border-indigo-300 shadow-2xs hover:shadow-md transition flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center text-xl transition transform group-hover:scale-105 mb-2.5">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-indigo-600 transition">Buka POS Kasir</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Layar transaksi penjualan</div>
        </a>

        <!-- Shortcut 2: Terima Mutasi -->
        <a href="{{ route('kasir.penerimaan.index') }}" class="group p-4 bg-white hover:bg-emerald-50/50 rounded-2xl border border-slate-200/80 hover:border-emerald-300 shadow-2xs hover:shadow-md transition flex flex-col items-center text-center relative">
            @if(isset($pendingMutasiCount) && $pendingMutasiCount > 0)
                <span class="absolute top-2.5 right-2.5 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-black flex items-center justify-center shadow-md animate-pulse">
                    {{ $pendingMutasiCount }}
                </span>
            @endif
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center text-xl transition transform group-hover:scale-105 mb-2.5">
                <i class="fa-solid fa-boxes-packing"></i>
            </div>
            <div class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-emerald-600 transition">Terima Mutasi</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Konfirmasi stok & kas fisik</div>
        </a>

        <!-- Shortcut 3: Minta Bantuan -->
        <a href="{{ route('kasir.request_operasional.index') }}" class="group p-4 bg-white hover:bg-amber-50/50 rounded-2xl border border-slate-200/80 hover:border-amber-300 shadow-2xs hover:shadow-md transition flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center text-xl transition transform group-hover:scale-105 mb-2.5">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-amber-600 transition">Minta Bantuan</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Request dana / operasional</div>
        </a>

        <!-- Shortcut 4: Blind Closing -->
        <a href="{{ route('kasir.closing.index') }}" class="group p-4 bg-white hover:bg-rose-50/50 rounded-2xl border border-slate-200/80 hover:border-rose-300 shadow-2xs hover:shadow-md transition flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white flex items-center justify-center text-xl transition transform group-hover:scale-105 mb-2.5">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="text-xs sm:text-sm font-extrabold text-slate-800 group-hover:text-rose-600 transition">Tutup Shift Kasir</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Laporan setoran akhir hari</div>
        </a>
    </div>

    <!-- 6. TABEL RIWAYAT TRANSAKSI (KOMPAK, RESPONSIF & PADAT) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        
        <!-- Header Tabel & Filter Toolbar -->
        <div class="p-4 sm:p-5 border-b border-slate-200/80 bg-slate-50/50 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-base sm:text-lg font-black text-slate-800 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-indigo-600 text-sm"></i>
                        <span>Riwayat Transaksi Penjualan</span>
                    </h2>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-100 text-indigo-700" x-text="filteredList.length + ' Data'"></span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi kasir dengan rincian produk, metode pembayaran, dan cetak struk.</p>
            </div>

            <!-- Toolbar Pencarian & Filter Multi-Kriteria -->
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                <!-- Live Search Box -->
                <div class="relative w-full sm:w-60">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 absolute left-3 top-2.5 text-xs"></i>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari ID, barang, kasir..." 
                           class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <!-- Filter Metode Pembayaran -->
                <select x-model="filterMetode" class="bg-white border border-slate-200 rounded-xl py-1.5 px-2.5 text-xs text-slate-700 focus:outline-none">
                    <option value="semua">Semua Pembayaran</option>
                    <option value="cash">💵 Tunai (Cash)</option>
                    <option value="qris">📱 QRIS</option>
                    <option value="transfer">🏦 Transfer</option>
                </select>

                <!-- Filter Tipe Transaksi -->
                <select x-model="filterTipe" class="bg-white border border-slate-200 rounded-xl py-1.5 px-2.5 text-xs text-slate-700 focus:outline-none">
                    <option value="semua">Semua Sumber</option>
                    <option value="mandiri">Kasir Mandiri</option>
                    <option value="admin">Via Bantuan Pusat</option>
                </select>

                <!-- Filter Tanggal Mulai & Sampai -->
                <div class="hidden sm:flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-2 py-1 text-xs">
                    <input type="date" x-model="startDate" class="border-0 p-0 text-xs text-slate-700 focus:ring-0">
                    <span class="text-slate-400 text-xs">-</span>
                    <input type="date" x-model="endDate" class="border-0 p-0 text-xs text-slate-700 focus:ring-0">
                </div>

                <!-- Tombol Reset Filter -->
                <button @click="resetFilter" 
                        x-show="searchQuery || startDate || endDate || filterTipe !== 'semua' || filterMetode !== 'semua'" 
                        class="px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-xl border border-rose-200 transition">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
            </div>
        </div>

        <!-- Tabel Kompak Berdensitas Tinggi -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 font-extrabold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-3.5 py-3 w-16">ID Struk</th>
                        <th class="px-3.5 py-3">Waktu & Petugas</th>
                        <th class="px-3.5 py-3">Rincian Item Produk</th>
                        <th class="px-3.5 py-3 text-center">Metode</th>
                        <th class="px-3.5 py-3 text-right">Diskon</th>
                        <th class="px-3.5 py-3 text-right">Total Tagihan</th>
                        <th class="px-3.5 py-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" x-show="paginatedData.length > 0">
                    <template x-for="transaksi in paginatedData" :key="transaksi.id">
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- ID Struk -->
                            <td class="px-3.5 py-2.5 font-mono font-bold text-slate-500 whitespace-nowrap">
                                <span class="bg-slate-100 px-1.5 py-0.5 rounded text-[11px]" x-text="'#' + transaksi.id"></span>
                            </td>

                            <!-- Waktu & Kasir -->
                            <td class="px-3.5 py-2.5 whitespace-nowrap">
                                <div class="font-bold text-slate-800" x-text="formatDate(transaksi.created_at)"></div>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-500 mt-0.5">
                                    <span class="font-medium text-slate-600" x-text="transaksi.kasir ? transaksi.kasir.name : 'Kasir'"></span>
                                    <template x-if="transaksi.is_bantuan_pusat">
                                        <span class="px-1.5 py-0.2 rounded font-bold bg-amber-100 text-amber-800">Bantuan Pusat</span>
                                    </template>
                                </div>
                            </td>

                            <!-- Ringkasan Produk Transaksi -->
                            <td class="px-3.5 py-2.5 max-w-xs sm:max-w-md truncate">
                                <div class="flex items-center gap-1 flex-wrap">
                                    <template x-for="(detail, idx) in (transaksi.detail_transaksi || []).slice(0, 3)" :key="detail.id">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-50 border border-slate-200 rounded text-[11px] text-slate-700">
                                            <b class="text-indigo-600" x-text="detail.kuantitas + 'x'"></b>
                                            <span x-text="detail.barang ? detail.barang.nama_barang : 'Item'"></span>
                                        </span>
                                    </template>
                                    <template x-if="(transaksi.detail_transaksi || []).length > 3">
                                        <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded" x-text="'+' + ((transaksi.detail_transaksi || []).length - 3) + ' lainnya'"></span>
                                    </template>
                                </div>
                            </td>

                            <!-- Metode Pembayaran -->
                            <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                <template x-if="transaksi.metode_pembayaran === 'cash'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-money-bill-wave text-[10px]"></i> Tunai
                                    </span>
                                </template>
                                <template x-if="transaksi.metode_pembayaran === 'qris'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-solid fa-qrcode text-[10px]"></i> QRIS
                                    </span>
                                </template>
                                <template x-if="transaksi.metode_pembayaran !== 'cash' && transaksi.metode_pembayaran !== 'qris'">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 uppercase" x-text="transaksi.metode_pembayaran">
                                    </span>
                                </template>
                            </td>

                            <!-- Total Diskon -->
                            <td class="px-3.5 py-2.5 text-right whitespace-nowrap font-mono">
                                <template x-if="(transaksi.total_diskon || 0) > 0">
                                    <span class="text-rose-600 font-bold" x-text="'-Rp ' + formatRupiah(transaksi.total_diskon)"></span>
                                </template>
                                <template x-if="!(transaksi.total_diskon || 0) || (transaksi.total_diskon || 0) <= 0">
                                    <span class="text-slate-300">-</span>
                                </template>
                            </td>

                            <!-- Total Tagihan -->
                            <td class="px-3.5 py-2.5 text-right whitespace-nowrap font-mono font-black text-slate-900" x-text="'Rp ' + formatRupiah(transaksi.total_tagihan)"></td>

                            <!-- Aksi Struk -->
                            <td class="px-3.5 py-2.5 text-center whitespace-nowrap">
                                <button @click="openDetail(transaksi)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition shadow-2xs">
                                    <i class="fa-solid fa-receipt text-[11px]"></i>
                                    <span>Struk</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State jika tidak ada data -->
        <div x-show="paginatedData.length === 0" class="py-12 px-4 text-center text-slate-400">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2 text-lg">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="font-bold text-slate-700 text-xs sm:text-sm">Belum ada riwayat transaksi yang cocok</div>
            <p class="text-[11px] text-slate-400 mt-0.5">Coba ubah kata kunci pencarian atau rentang tanggal yang dipilih.</p>
        </div>

        <!-- Footer Pagination & Pilihan Baris per Halaman -->
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <span>Tampilkan:</span>
                    <select x-model.number="itemsPerPage" @change="currentPage = 1" class="border border-slate-200 rounded-lg px-2 py-1 text-xs bg-white text-slate-700 focus:outline-none">
                        <option value="10">10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                        <option value="100">100 Baris</option>
                    </select>
                </div>
                <span>
                    Menampilkan <b class="text-slate-800" x-text="paginatedData.length"></b> dari <b class="text-slate-800" x-text="filteredList.length"></b> riwayat
                </span>
            </div>

            <!-- Tombol Navigasi Halaman -->
            <div class="flex items-center gap-1.5">
                <button @click="prevPage" 
                        :disabled="currentPage === 1" 
                        class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-bold transition disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 text-xs flex items-center gap-1">
                    <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    <span>Sebelumnya</span>
                </button>
                <span class="px-3 py-1.5 font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg text-xs font-mono" x-text="currentPage + ' / ' + totalPages"></span>
                <button @click="nextPage" 
                        :disabled="currentPage >= totalPages" 
                        class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-bold transition disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-100 text-xs flex items-center gap-1">
                    <span>Berikutnya</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- 7. MODAL DETAIL STRUK THERMAL (RETAIL AUTHENTIC) -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-3 py-6 text-center sm:p-0">
            
            <div x-show="showModal" 
                 x-transition.opacity 
                 @click="closeDetail" 
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

            <div x-show="showModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="inline-block align-bottom receipt-paper bg-white rounded-3xl text-left overflow-hidden shadow-2xl sm:my-8 sm:align-middle sm:max-w-sm w-full relative z-50 border border-slate-200">
                
                <!-- Receipt Header -->
                <div class="p-6 text-center border-b-2 border-dashed border-slate-200 relative bg-slate-50/50">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center mx-auto mb-2 text-xl shadow-md">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-800 tracking-tight">RUMAH KUOTA POS</h3>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider" x-text="'Cabang: ' + '{{ $cabang }}'"></div>
                    <div class="text-[10px] text-slate-400 mt-1 font-mono" x-text="selectedTransaksi ? formatDate(selectedTransaksi.created_at) : ''"></div>
                    <div class="mt-1 font-mono font-bold text-xs text-indigo-700" x-text="selectedTransaksi ? 'Struk #' + selectedTransaksi.id : ''"></div>
                </div>

                <!-- Receipt Body (Line Items) -->
                <div class="p-5 max-h-[50vh] overflow-y-auto space-y-3 font-mono text-xs">
                    <template x-if="selectedTransaksi">
                        <div>
                            <!-- Daftar Item -->
                            <div class="space-y-2.5 pb-3 border-b border-dashed border-slate-200">
                                <template x-for="detail in selectedTransaksi.detail_transaksi" :key="detail.id">
                                    <div>
                                        <div class="font-bold text-slate-800" x-text="detail.barang ? detail.barang.nama_barang : 'Item'"></div>
                                        
                                        <!-- Jika Digital B Cash Out -->
                                        <template x-if="detail.barang && detail.barang.jenis_transaksi === 'cash_out'">
                                            <div class="p-2 bg-slate-50 rounded border border-slate-200 text-[11px] space-y-1 mt-1">
                                                <div class="flex justify-between text-slate-600">
                                                    <span>Nominal Tarik:</span>
                                                    <span class="font-bold" x-text="'Rp ' + formatRupiah(detail.hpp_satuan)"></span>
                                                </div>
                                                <div class="flex justify-between text-slate-600">
                                                    <span>Biaya Admin Fee:</span>
                                                    <span class="font-bold" x-text="'Rp ' + formatRupiah(detail.harga_jual_final - detail.hpp_satuan)"></span>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Item Reguler -->
                                        <template x-if="!detail.barang || detail.barang.jenis_transaksi !== 'cash_out'">
                                            <div class="flex justify-between text-slate-600 text-[11px] mt-0.5">
                                                <span x-text="detail.kuantitas + ' x Rp ' + formatRupiah(detail.harga_jual_final)"></span>
                                                <span class="font-bold text-slate-800" x-text="'Rp ' + formatRupiah(detail.kuantitas * detail.harga_jual_final)"></span>
                                            </div>
                                        </template>

                                        <!-- Diskon per item jika ada -->
                                        <template x-if="(detail.diskon || 0) > 0">
                                            <div class="flex justify-between text-[11px] text-rose-600 font-bold">
                                                <span>Diskon Item:</span>
                                                <span x-text="'-Rp ' + formatRupiah(detail.diskon)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <!-- Financial Summary Breakdown -->
                            <div class="pt-3 space-y-1.5 text-xs">
                                <div class="flex justify-between text-slate-600">
                                    <span>Subtotal:</span>
                                    <span class="font-bold text-slate-800" x-text="'Rp ' + formatRupiah((selectedTransaksi?.total_tagihan || 0) + (selectedTransaksi?.total_diskon || 0))"></span>
                                </div>
                                <template x-if="(selectedTransaksi?.total_diskon || 0) > 0">
                                    <div class="flex justify-between text-rose-600 font-bold">
                                        <span>Total Potongan:</span>
                                        <span x-text="'-Rp ' + formatRupiah(selectedTransaksi?.total_diskon)"></span>
                                    </div>
                                </template>
                                
                                <div class="flex justify-between items-baseline pt-2 border-t-2 border-dashed border-slate-300">
                                    <span class="font-black text-slate-800 text-sm uppercase">Total Akhir</span>
                                    <span class="font-black text-indigo-700 text-lg" x-text="'Rp ' + formatRupiah(selectedTransaksi?.total_tagihan)"></span>
                                </div>
                            </div>

                            <!-- Payment Breakdown -->
                            <div class="mt-3 p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-1">
                                <div class="flex justify-between text-slate-600">
                                    <span>Metode:</span>
                                    <span class="font-bold uppercase text-slate-800" x-text="selectedTransaksi?.metode_pembayaran"></span>
                                </div>
                                <template x-if="selectedTransaksi?.metode_pembayaran === 'cash'">
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-slate-600">
                                            <span>Uang Diterima:</span>
                                            <span class="font-bold text-slate-800" x-text="'Rp ' + formatRupiah(selectedTransaksi?.uang_diterima)"></span>
                                        </div>
                                        <div class="flex justify-between text-slate-600">
                                            <span>Kembalian:</span>
                                            <span class="font-bold text-emerald-600" x-text="'Rp ' + formatRupiah(selectedTransaksi?.kembalian)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="text-center text-[10px] text-slate-400 mt-4 leading-relaxed">
                                Terima kasih atas kunjungan Anda!<br>
                                Barang yang sudah dibeli tidak dapat ditukar kecuali ada perjanjian.
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Receipt Modal Footer Buttons -->
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center gap-2">
                    <button @click="window.print()" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak Struk</span>
                    </button>
                    <button @click="closeDetail" class="py-2.5 px-4 bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-xl text-xs border border-slate-200 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function riwayatTransaksiData() {
        return {
            rawList: @json($riwayatTransaksi ?? []),
            searchQuery: '',
            startDate: '',
            endDate: '',
            filterTipe: 'semua',
            filterMetode: 'semua',
            currentPage: 1,
            itemsPerPage: 10, // Diperbarui dari 5 menjadi 10 agar muat banyak dan proporsional
            showModal: false,
            selectedTransaksi: null,
            
            get filteredList() {
                let list = this.rawList;
                
                // Filter Pencarian Teks (ID, Kasir, Nama Barang, dll)
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    list = list.filter(t => {
                        const idMatch = t.id.toString().includes(q);
                        const kasirMatch = t.kasir && t.kasir.name.toLowerCase().includes(q);
                        const metodeMatch = t.metode_pembayaran && t.metode_pembayaran.toLowerCase().includes(q);
                        const itemMatch = t.detail_transaksi && t.detail_transaksi.some(d => d.barang && d.barang.nama_barang.toLowerCase().includes(q));
                        return idMatch || kasirMatch || metodeMatch || itemMatch;
                    });
                }

                // Filter Rentang Tanggal
                if (this.startDate) {
                    let start = new Date(this.startDate);
                    start.setHours(0,0,0,0);
                    list = list.filter(t => new Date(t.created_at) >= start);
                }
                if (this.endDate) {
                    let end = new Date(this.endDate);
                    end.setHours(23,59,59,999);
                    list = list.filter(t => new Date(t.created_at) <= end);
                }

                // Filter Tipe Transaksi
                if (this.filterTipe === 'mandiri') {
                    list = list.filter(t => !t.is_bantuan_pusat);
                } else if (this.filterTipe === 'admin') {
                    list = list.filter(t => t.is_bantuan_pusat);
                }

                // Filter Metode Pembayaran
                if (this.filterMetode !== 'semua') {
                    list = list.filter(t => t.metode_pembayaran === this.filterMetode);
                }

                return list;
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredList.length / this.itemsPerPage));
            },
            
            get paginatedData() {
                const start = (this.currentPage - 1) * this.itemsPerPage;
                const end = start + this.itemsPerPage;
                return this.filteredList.slice(start, end);
            },
            
            resetFilter() {
                this.searchQuery = '';
                this.startDate = '';
                this.endDate = '';
                this.filterTipe = 'semua';
                this.filterMetode = 'semua';
                this.currentPage = 1;
            },

            nextPage() {
                if (this.currentPage < this.totalPages) this.currentPage++;
            },
            
            prevPage() {
                if (this.currentPage > 1) this.currentPage--;
            },
            
            formatDate(dateStr) {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
                return date.toLocaleDateString('id-ID', options).replace(/\./g, ':');
            },
            
            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number || 0);
            },
            
            openDetail(transaksi) {
                this.selectedTransaksi = transaksi;
                this.showModal = true;
            },
            
            closeDetail() {
                this.showModal = false;
                this.selectedTransaksi = null;
            }
        }
    }
</script>

@if(session('has_minus'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let minusDetails = {!! json_encode(session('minus_details')) !!};
        let minusHtml = '<ul class="text-left text-rose-700 font-bold bg-rose-50 p-4 rounded-xl list-disc list-inside text-xs sm:text-sm">';
        minusDetails.forEach(detail => {
            minusHtml += `<li>- ${detail}</li>`;
        });
        minusHtml += '</ul><br><p class="text-xs text-slate-500">Catatan: Anda dapat mengembalikan uang/saldo minus ini melalui menu "Riwayat Minus (Reimburse)".</p>';

        Swal.fire({
            title: 'Perhatian! Terdapat Closing Minus',
            html: minusHtml,
            icon: 'warning',
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Saya Mengerti'
        });
    });
</script>
@endif
@endpush
