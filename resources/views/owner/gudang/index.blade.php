@extends('layouts.app')
@section('title', 'Monitoring Aset Gudang & Cabang')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Mencegah konflik dengan gaya global app.blade.php -->
    <style>
        .sidebar { z-index: 50; }
        .main-content { min-width: 0; }
    </style>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <!-- HEADER -->
    <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Monitoring Fisik & Keuangan</h1>
            <p class="text-gray-500 mt-1">Laporan terintegrasi Aset Fisik & Saldo Keuangan di Pusat serta seluruh Cabang.</p>
        </div>
    </div>

    <!-- TABS -->
    <div class="bg-white rounded-t-xl border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500" id="asetTab" data-tabs-toggle="#asetTabContent" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg aria-selected:text-blue-600 aria-selected:border-blue-600 hover:text-gray-600 hover:border-gray-300 transition" id="pusat-tab" data-tabs-target="#pusat" type="button" role="tab" aria-controls="pusat" aria-selected="true">Gudang Pusat</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg aria-selected:text-green-600 aria-selected:border-green-600 hover:text-gray-600 hover:border-gray-300 transition" id="cabang-tab" data-tabs-target="#cabang" type="button" role="tab" aria-controls="cabang" aria-selected="false">Distribusi Cabang</button>
            </li>
        </ul>
    </div>

    <div id="asetTabContent" class="bg-white rounded-b-xl shadow-sm border border-t-0 border-gray-200 mb-8 p-6">
        
        <!-- TAB 1: PUSAT -->
        <div class="hidden" id="pusat" role="tabpanel" aria-labelledby="pusat-tab"
             x-data="{ 
                items: @js($stokPusatGrouped), 
                searchPusat: '', 
                filterTipePusat: '', 
                sortOrderPusat: 'terbaru',
                get filteredAndSortedPusat() {
                    let result = this.items.filter(i => 
                        (this.searchPusat === '' || i.nama_barang.toLowerCase().includes(this.searchPusat.toLowerCase())) && 
                        (this.filterTipePusat === '' || i.tipe_barang === this.filterTipePusat)
                    );
                    
                    if (this.sortOrderPusat === 'terlama') {
                        result.sort((a, b) => new Date(a.oldest_batch_date) - new Date(b.oldest_batch_date));
                    } else if (this.sortOrderPusat === 'terbaru') {
                        result.sort((a, b) => new Date(b.newest_batch_date) - new Date(a.newest_batch_date));
                    }
                    
                    return result;
                }
             }">
            <!-- WIDGETS PUSAT (BARANG FISIK) -->
            <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Aset Barang Fisik Pusat</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-blue-50 p-5 rounded-xl border-l-4 border-blue-500 shadow-sm">
                    <p class="text-xs font-bold text-blue-500 uppercase tracking-wider mb-1">Total Aset (HPP)</p>
                    <h3 class="text-2xl font-black text-gray-800">Rp {{ number_format($totalAsetModalPusat, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Modal tertahan dalam fisik barang</p>
                </div>
                <div class="bg-green-50 p-5 rounded-xl border-l-4 border-green-500 shadow-sm">
                    <p class="text-xs font-bold text-green-500 uppercase tracking-wider mb-1">Total Aset (Nilai Jual)</p>
                    <h3 class="text-2xl font-black text-green-700">Rp {{ number_format($totalAsetJualPusat, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Potensi omzet jika barang laku</p>
                </div>
                <div class="bg-orange-50 p-5 rounded-xl border-l-4 border-orange-500 shadow-sm">
                    <p class="text-xs font-bold text-orange-500 uppercase tracking-wider mb-1">Potensi Laba Kotor</p>
                    <h3 class="text-2xl font-black text-orange-600">Rp {{ number_format($totalAsetJualPusat - $totalAsetModalPusat, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Selisih HPP & Nilai Jual Pusat</p>
                </div>
            </div>

            <!-- WIDGETS PUSAT (UANG FISIK & DIGITAL) -->
            <h4 class="font-bold text-gray-700 mb-3 border-b pb-2 mt-8">Aset Keuangan Pusat</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-teal-50 p-5 rounded-xl border-l-4 border-teal-500 shadow-sm">
                    <p class="text-xs font-bold text-teal-500 uppercase tracking-wider mb-1">Uang Fisik Pusat (Brankas)</p>
                    <h3 class="text-2xl font-black text-gray-800">Rp {{ number_format($kasPusat, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Total uang tunai yang ada di Pusat</p>
                </div>
                <div class="bg-purple-50 p-5 rounded-xl border-l-4 border-purple-500 shadow-sm relative group">
                    <p class="text-xs font-bold text-purple-500 uppercase tracking-wider mb-1">Saldo Digital Pusat (APK)</p>
                    <h3 class="text-2xl font-black text-purple-700 cursor-pointer hover:text-purple-900 transition flex items-center gap-2" @click="$dispatch('load-apk-modal', { title: 'Rincian Saldo APK - Gudang Pusat', items: {{ Js::from($detailApkPusat->map(function($a) { return ['nama_layanan' => $a->masterAplikasiLayanan->nama_layanan, 'jenis_layanan' => $a->masterAplikasiLayanan->jenis_layanan, 'nomor_rekening' => $a->nomor_rekening, 'saldo_sekarang' => $a->saldo_sekarang, 'apk_id' => $a->id, 'type' => 'pusat']; })) }} })">
                        Rp {{ number_format($saldoApkPusat, 0, ',', '.') }}
                        <svg class="w-5 h-5 text-purple-400 group-hover:text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Total saldo e-Wallet / Bank Pusat</p>
                </div>
                <div class="bg-gray-50 p-5 rounded-xl border-l-4 border-gray-600 shadow-sm">
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Total Keuangan Pusat</p>
                    <h3 class="text-2xl font-black text-gray-800">Rp {{ number_format($totalAsetKeuanganPusat, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Gabungan uang fisik & saldo digital</p>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="flex flex-col md:flex-row gap-3 w-full">
                    <input type="text" x-model="searchPusat" placeholder="Cari nama barang..." class="border border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none w-full md:w-64">
                    <select x-model="filterTipePusat" class="border border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">Semua Tipe Barang</option>
                        <option value="Fisik ACC">Fisik ACC</option>
                        <option value="Fisik Voucher Kartu">Fisik Voucher Kartu</option>
                    </select>
                    <select x-model="sortOrderPusat" class="border border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="terbaru">Terbaru (Berdasarkan Batch)</option>
                        <option value="terlama">Terlama (Berdasarkan Batch)</option>
                    </select>
                </div>
            </div>

            <!-- TABLE PUSAT -->
            <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Barang</th>
                            <th scope="col" class="px-6 py-3 text-right">Total Stok Fisik</th>
                            <th scope="col" class="px-6 py-3 text-right">Total Aset Modal</th>
                            <th scope="col" class="px-6 py-3 text-right">Potensi Laba</th>
                            <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in filteredAndSortedPusat" :key="item.barang_id">
                            <tr class="border-b hover:bg-gray-50" :class="item.is_kritis ? 'bg-red-50' : 'bg-white'">
                                <td class="px-6 py-4">
                                    <div class="font-bold" :class="item.is_kritis ? 'text-red-800' : 'text-blue-600'">
                                        <span x-text="item.nama_barang"></span>
                                        <template x-if="item.is_kritis">
                                            <span class="ml-2 inline-block text-[10px] bg-red-600 text-white px-1.5 py-0.5 rounded font-black uppercase tracking-wider">EWS Kritis</span>
                                        </template>
                                    </div>
                                    <div class="text-xs font-semibold mt-1">
                                        <span class="bg-gray-100 px-2 py-0.5 rounded" :class="item.is_kritis ? 'text-red-600' : 'text-gray-600'" x-text="item.kategori"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold" :class="item.is_kritis ? 'text-red-600' : 'text-indigo-600'" x-text="new Intl.NumberFormat('id-ID').format(item.total_stok)"></td>
                                <td class="px-6 py-4 text-right" :class="item.is_kritis ? 'text-red-800' : ''" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.total_aset_modal)"></td>
                                <td class="px-6 py-4 text-right font-black" :class="item.is_kritis ? 'text-red-900' : 'text-orange-600'" x-text="'+ Rp ' + new Intl.NumberFormat('id-ID').format(item.potensi_laba)"></td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" @click="$dispatch('load-pusat-modal', { batches: item.batches, barangName: item.nama_barang })" class="inline-flex items-center gap-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Detail Batch
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredAndSortedPusat.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada barang di Gudang Pusat yang cocok.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: CABANG -->
        <div class="hidden" id="cabang" role="tabpanel" aria-labelledby="cabang-tab" x-data="{ mode: 'fisik', search: '', filterArea: '' }">
            
            <!-- TOGGLES & FILTERS -->
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="flex bg-gray-100 p-1 rounded-lg">
                    <button @click="mode = 'fisik'" :class="mode === 'fisik' ? 'bg-white shadow text-indigo-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-2 text-sm font-bold rounded-md transition-all">Stok Fisik</button>
                    <button @click="mode = 'kas'" :class="mode === 'kas' ? 'bg-white shadow text-green-600' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-2 text-sm font-bold rounded-md transition-all">Kas Laci</button>
                </div>
                <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                    <input type="text" x-model="search" placeholder="Cari cabang..." class="border border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none w-full md:w-64">
                    <select x-model="filterArea" class="border border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                        <option value="">Semua Area</option>
                        @php
                            $areas = collect($rekapCabang)->map(fn($c) => $c['cabang']->area)->filter()->unique();
                        @endphp
                        @foreach($areas as $a)
                            <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- WIDGETS CABANG -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" x-show="mode === 'fisik'">
                <div class="bg-indigo-50 p-5 rounded-xl border-l-4 border-indigo-500 shadow-sm">
                    <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1">Total Aset Cabang (HPP)</p>
                    <h3 class="text-2xl font-black text-gray-800">Rp {{ number_format($grandTotalModalCabang, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Total modal mengendap di seluruh cabang</p>
                </div>
                <div class="bg-teal-50 p-5 rounded-xl border-l-4 border-teal-500 shadow-sm">
                    <p class="text-xs font-bold text-teal-500 uppercase tracking-wider mb-1">Total Aset Cabang (Nilai Jual)</p>
                    <h3 class="text-2xl font-black text-teal-700">Rp {{ number_format($grandTotalJualCabang, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Total potensi omzet di seluruh cabang</p>
                </div>
            </div>

            <!-- WIDGETS CABANG (KAS & DIGITAL) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" x-show="mode === 'kas'" style="display: none;">
                <div class="bg-teal-50 p-5 rounded-xl border-l-4 border-teal-500 shadow-sm">
                    <p class="text-xs font-bold text-teal-500 uppercase tracking-wider mb-1">Total Uang Fisik (Laci Kasir)</p>
                    <h3 class="text-2xl font-black text-gray-800">Rp {{ number_format($grandTotalKasCabang, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Total uang tunai di seluruh laci cabang</p>
                </div>
                <div class="bg-purple-50 p-5 rounded-xl border-l-4 border-purple-500 shadow-sm">
                    <p class="text-xs font-bold text-purple-500 uppercase tracking-wider mb-1">Total Saldo Digital Cabang</p>
                    <h3 class="text-2xl font-black text-purple-700">Rp {{ number_format($grandTotalApkCabang, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">Total saldo E-Wallet / Bank di seluruh cabang</p>
                </div>
            </div>

            <!-- TABLE CABANG FISIK -->
            <div class="overflow-x-auto border border-gray-100 rounded-lg" x-show="mode === 'fisik'">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Cabang</th>
                            <th scope="col" class="px-6 py-3 text-right">Total Jenis Barang</th>
                            <th scope="col" class="px-6 py-3 text-right">Total Aset (HPP)</th>
                            <th scope="col" class="px-6 py-3 text-right">Total Aset (Nilai Jual)</th>
                            <th scope="col" class="px-6 py-3 text-right">Potensi Laba</th>
                            <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapCabang as $cab)
                        <tr class="bg-white border-b hover:bg-gray-50" x-show="(search === '' || '{{ strtolower($cab['cabang']->nama_cabang) }}'.includes(search.toLowerCase())) && (filterArea === '' || '{{ $cab['cabang']->area }}' === filterArea)">
                            <td class="px-6 py-4 font-bold text-gray-900">
                                {{ $cab['cabang']->nama_cabang }}
                                @if($cab['has_ews'])
                                    <a href="{{ route('admin.request_operasional.index') }}" class="inline-flex items-center ml-2 px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-800 hover:bg-red-200 uppercase tracking-wider transition">
                                        🚨 {{ $cab['ews_count'] }} Kritis
                                    </a>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-indigo-600">{{ number_format($cab['total_barang'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-black text-gray-800">Rp {{ number_format($cab['aset_modal'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-teal-600">Rp {{ number_format($cab['aset_jual'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">+ Rp {{ number_format($cab['potensi_laba'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" onclick="openCabangModal({{ $cab['cabang']->id }}, '{{ $cab['cabang']->nama_cabang }}')" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 hover:text-indigo-900 px-3 py-1.5 rounded-lg text-xs font-bold transition">Cek Detail</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada data cabang.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- TABLE CABANG KAS & DIGITAL -->
            <div class="overflow-x-auto border border-gray-100 rounded-lg" x-show="mode === 'kas'" style="display: none;">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nama Cabang</th>
                            <th scope="col" class="px-6 py-3">Area</th>
                            <th scope="col" class="px-6 py-3 text-right">Uang Fisik Kasir (Rp)</th>
                            <th scope="col" class="px-6 py-3 text-right">Saldo Digital APK (Rp)</th>
                            <th scope="col" class="px-6 py-3 text-right">Total Dana Cabang (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekapCabang as $cab)
                        <tr class="bg-white border-b hover:bg-gray-50" x-show="(search === '' || '{{ strtolower($cab['cabang']->nama_cabang) }}'.includes(search.toLowerCase())) && (filterArea === '' || '{{ $cab['cabang']->area }}' === filterArea)">
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $cab['cabang']->nama_cabang }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $cab['cabang']->area ?: '-' }}</td>
                            <td class="px-6 py-4 text-right font-black text-green-700">{{ number_format($cab['saldo_kas'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-black text-purple-700 group">
                                <button type="button" @click="$dispatch('load-apk-modal', { title: 'Rincian Saldo APK - Cabang {{ addslashes($cab['cabang']->nama_cabang) }}', items: {{ Js::from($cab['cabang']->aplikasiLayanan->map(function($a) { return ['nama_layanan' => $a->masterAplikasiLayanan->nama_layanan, 'jenis_layanan' => $a->masterAplikasiLayanan->jenis_layanan, 'nomor_rekening' => $a->nomor_rekening, 'saldo_sekarang' => $a->saldo_sekarang, 'apk_id' => $a->id, 'type' => 'cabang']; })) }} })" class="inline-flex items-center gap-1 hover:text-purple-900 focus:outline-none transition-colors">
                                    {{ number_format($cab['saldo_apk'], 0, ',', '.') }}
                                    <svg class="w-4 h-4 text-purple-400 group-hover:text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-gray-900">{{ number_format($cab['saldo_kas'] + $cab['saldo_apk'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada data cabang.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Detail Cabang -->
<div id="cabangModal" class="hidden fixed inset-0 z-[100] bg-gray-900 bg-opacity-50 flex justify-center items-center p-4" 
     x-data="{ items: [], searchItem: '', filterTipe: '' }" 
     @load-modal-data.window="items = $event.detail.items; searchItem = ''; filterTipe = ''; document.getElementById('modalCabangTitle').textContent = 'Detail Stok: ' + $event.detail.cabangName; document.getElementById('cabangModal').classList.remove('hidden');" 
     @close-modal.window="document.getElementById('cabangModal').classList.add('hidden')">
     
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800" id="modalCabangTitle">Detail Stok Cabang</h3>
            <button type="button" onclick="closeCabangModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="px-6 py-3 bg-white border-b border-gray-100 flex gap-4">
            <input type="text" x-model="searchItem" placeholder="Cari nama barang..." class="border border-gray-200 rounded p-2 text-sm flex-1 focus:outline-none focus:border-indigo-500">
            <select x-model="filterTipe" class="border border-gray-200 rounded p-2 text-sm focus:outline-none bg-white">
                <option value="">Semua Fisik</option>
                <option value="Fisik ACC">Fisik ACC</option>
                <option value="Fisik Voucher Kartu">Fisik Voucher Kartu</option>
            </select>
        </div>
        <div class="p-6 overflow-y-auto bg-gray-50">
            <div id="modalLoading" class="text-center py-8 text-gray-500 hidden">
                Memuat data stok cabang...
            </div>
            <div id="modalContent">
                <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-4 py-3">Nama Barang</th>
                                <th scope="col" class="px-4 py-3 text-right">Stok Fisik</th>
                                <th scope="col" class="px-4 py-3 text-right">Subtotal Modal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in items.filter(i => (searchItem === '' || i.nama_barang.toLowerCase().includes(searchItem.toLowerCase())) && (filterTipe === '' || i.tipe_barang === filterTipe))" :key="item.id">
                                <tr class="border-b hover:bg-gray-50" :class="item.is_kritis ? 'bg-red-50' : 'bg-white'">
                                    <td class="px-4 py-3">
                                        <div class="font-bold" :class="item.is_kritis ? 'text-red-900' : 'text-gray-900'">
                                            <span x-text="item.nama_barang"></span>
                                            <template x-if="item.is_kritis">
                                                <a href="{{ route('distribusi.mutasi.index') }}" class="ml-2 inline-block text-[10px] bg-red-600 text-white px-1.5 py-0.5 rounded font-black uppercase tracking-wider hover:bg-red-700 transition">EWS Kritis (Restok)</a>
                                            </template>
                                        </div>
                                        <div class="text-xs" :class="item.is_kritis ? 'text-red-700' : 'text-gray-500'" x-text="item.tipe_barang + ' / ' + item.kategori"></div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold" :class="item.is_kritis ? 'text-red-600' : 'text-indigo-600'" x-text="item.stok"></td>
                                    <td class="px-4 py-3 text-right font-black" :class="item.is_kritis ? 'text-red-900' : 'text-gray-800'" x-text="item.subtotal_modal"></td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500">Cabang ini tidak memiliki stok barang fisik.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Gudang Pusat -->
<div id="pusatModal" class="hidden fixed inset-0 z-[100] bg-gray-900 bg-opacity-50 flex justify-center items-center p-4" 
     x-data="{ batches: [], searchBatch: '', filterSupplier: '' }" 
     @load-pusat-modal.window="batches = $event.detail.batches; searchBatch = ''; filterSupplier = ''; document.getElementById('modalPusatTitle').textContent = 'Detail Batch: ' + $event.detail.barangName; document.getElementById('pusatModal').classList.remove('hidden');" 
     @close-pusat-modal.window="document.getElementById('pusatModal').classList.add('hidden')">
     
    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800" id="modalPusatTitle">Detail Batch Barang</h3>
            <button type="button" @click="$dispatch('close-pusat-modal')" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="px-6 py-3 bg-white border-b border-gray-100 flex gap-4">
            <input type="text" x-model="searchBatch" placeholder="Cari kode batch..." class="border border-gray-200 rounded p-2 text-sm flex-1 focus:outline-none focus:border-indigo-500">
            <input type="text" x-model="filterSupplier" placeholder="Cari supplier..." class="border border-gray-200 rounded p-2 text-sm flex-1 focus:outline-none focus:border-indigo-500">
        </div>
        <div class="p-6 overflow-y-auto bg-gray-50">
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-4 py-3">Batch Beli</th>
                            <th scope="col" class="px-4 py-3">Tanggal Masuk (Usia)</th>
                            <th scope="col" class="px-4 py-3">Supplier</th>
                            <th scope="col" class="px-4 py-3 text-right">Stok Sisa</th>
                            <th scope="col" class="px-4 py-3 text-right">Harga Modal Satuan</th>
                            <th scope="col" class="px-4 py-3 text-right">Subtotal Aset Modal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="b in batches.filter(i => (searchBatch === '' || i.kode_batch.toLowerCase().includes(searchBatch.toLowerCase())) && (filterSupplier === '' || i.supplier.toLowerCase().includes(filterSupplier.toLowerCase())))" :key="b.kode_batch">
                            <tr class="border-b hover:bg-gray-50 bg-white">
                                <td class="px-4 py-3 font-bold text-gray-900" x-text="b.kode_batch"></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800" x-text="b.tanggal_masuk"></div>
                                    <div class="text-xs text-gray-500"><span x-text="b.usia_hari"></span> Hari di Gudang</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700" x-text="b.supplier"></td>
                                <td class="px-4 py-3 text-right font-bold text-indigo-600" x-text="new Intl.NumberFormat('id-ID').format(b.stok)"></td>
                                <td class="px-4 py-3 text-right" x-text="b.harga_modal"></td>
                                <td class="px-4 py-3 text-right font-black text-gray-800" x-text="b.subtotal_modal"></td>
                            </tr>
                        </template>
                        <tr x-show="batches.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data batch.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rincian APK -->
<div id="apkModal" class="hidden fixed inset-0 z-[110] bg-gray-900 bg-opacity-50 flex justify-center items-center p-4" 
     x-data="{ items: [], title: '' }" 
     @load-apk-modal.window="items = $event.detail.items; title = $event.detail.title; document.getElementById('apkModal').classList.remove('hidden');" 
     @close-apk-modal.window="document.getElementById('apkModal').classList.add('hidden')">
     
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-purple-50">
            <h3 class="text-lg font-bold text-purple-800" x-text="title">Rincian Saldo APK</h3>
            <button type="button" @click="$dispatch('close-apk-modal')" class="text-gray-400 hover:text-gray-600 p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto bg-gray-50">
            <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="px-4 py-3">Aplikasi / Bank</th>
                            <th scope="col" class="px-4 py-3">No. Rekening</th>
                            <th scope="col" class="px-4 py-3 text-right">Saldo Saat Ini</th>
                            <th scope="col" class="px-4 py-3 text-center">Riwayat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="item in items" :key="item.apk_id">
                            <tr class="border-b bg-white hover:bg-purple-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900" x-text="item.nama_layanan"></div>
                                    <div class="text-xs text-gray-500" x-text="item.jenis_layanan"></div>
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-700" x-text="item.nomor_rekening || '-'"></td>
                                <td class="px-4 py-3 text-right font-black text-purple-700" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(item.saldo_sekarang)"></td>
                                <td class="px-4 py-3 text-center">
                                    <a :href="'/owner/riwayat-apk?type=' + item.type + '&id=' + item.apk_id" class="inline-flex items-center gap-1 bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                        Riwayat
                                    </a>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="items.length === 0">
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">Belum ada data aplikasi layanan/bank.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="px-6 py-4 bg-white border-t border-gray-100 flex justify-end">
            <button type="button" @click="$dispatch('close-apk-modal')" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-5 py-2 rounded-lg font-bold transition">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openCabangModal(cabangId, cabangName) {
        document.getElementById('cabangModal').classList.remove('hidden');
        document.getElementById('modalLoading').classList.remove('hidden');
        document.getElementById('modalContent').classList.add('hidden');
        
        // Dispatch event with empty items to clear old modal state first
        window.dispatchEvent(new CustomEvent('load-modal-data', { detail: { items: [], cabangName: cabangName } }));
        
        fetch(`/owner/gudang/cabang/${cabangId}/detail`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modalLoading').classList.add('hidden');
                document.getElementById('modalContent').classList.remove('hidden');
                // Pass JSON directly to Alpine via custom event
                window.dispatchEvent(new CustomEvent('load-modal-data', { detail: { items: data, cabangName: cabangName } }));
            })
            .catch(err => {
                document.getElementById('modalLoading').textContent = 'Gagal memuat data. Silakan coba lagi.';
                console.error(err);
            });
    }
    
    function closeCabangModal() {
        window.dispatchEvent(new Event('close-modal'));
    }

    // Simple tab implementation without Flowbite JS dependency if it's missing
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('[data-tabs-target]');
        const tabContents = document.querySelectorAll('[role="tabpanel"]');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active classes from all tabs
                tabs.forEach(t => {
                    t.setAttribute('aria-selected', 'false');
                });
                // Add active class to clicked tab
                tab.setAttribute('aria-selected', 'true');

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Show target tab content
                const target = document.querySelector(tab.dataset.tabsTarget);
                target.classList.remove('hidden');
            });
        });
    });
</script>
@endpush
@endsection
