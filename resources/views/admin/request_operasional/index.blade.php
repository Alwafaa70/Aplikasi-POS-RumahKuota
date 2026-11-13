@extends('layouts.app')
@section('title', 'Kelola Request Operasional (Pusat)')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* Custom Scrollbar for better UI */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

@section('content')
<div class="p-6 bg-slate-50 min-h-screen" x-data="requestManager()" x-init="initData()">
    <!-- Header Section -->
    <div class="mb-8 bg-gradient-to-r from-blue-700 to-indigo-800 rounded-2xl p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10">
            <h2 class="text-3xl font-extrabold mb-2 tracking-tight">Kelola Request Cabang</h2>
            <p class="text-blue-100 text-sm max-w-xl">Pusat kendali untuk memproses seluruh Request Operasional dan Bantuan Transaksi Digital B dari seluruh cabang secara terpusat.</p>
        </div>
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-10 -translate-y-10">
            <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 9h-2V7h-2v5H6v2h2v5h2v-5h2v-2z"/></svg>
        </div>
    </div>

    <!-- Branch Cards (Only show branches with pending requests) -->
    <div class="flex gap-4 mb-8 overflow-x-auto pb-4 pt-2 hide-scrollbar" x-show="Object.keys(pendingCounts).length > 0">
        <div class="flex-shrink-0 w-48 bg-white border border-gray-200 rounded-xl p-5 cursor-pointer transition-all duration-300 hover:shadow-lg hover:border-blue-400 group" 
             :class="{'ring-2 ring-blue-500 bg-blue-50': filterCabang === ''}" 
             @click="filterCabang = ''">
            <h4 class="font-bold text-gray-800 group-hover:text-blue-600 transition-colors">Semua Cabang</h4>
            <p class="text-xs text-gray-500 mt-1">Tampilkan Seluruhnya</p>
        </div>
        
        <template x-for="(count, branchId) in pendingCounts" :key="branchId">
            <div class="flex-shrink-0 w-56 bg-white border border-gray-200 rounded-xl p-5 cursor-pointer transition-all duration-300 hover:shadow-lg relative group"
                 :class="{'ring-2 ring-blue-500 bg-blue-50': filterCabang === branchId}" 
                 @click="filterCabang = branchId">
                <div class="absolute -top-3 -right-3 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-xs font-bold shadow-md animate-bounce" x-text="count"></div>
                <h4 class="font-bold text-gray-800 group-hover:text-blue-600 transition-colors truncate" x-text="branchNames[branchId]"></h4>
                <p class="text-xs text-red-500 mt-1 font-semibold flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Perlu Diproses
                </p>
            </div>
        </template>
    </div>

    <!-- Filters -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Status Request</label>
            <select x-model="filterStatus" class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 outline-none transition-all">
                <option value="">Semua Status</option>
                <option value="pending">Menunggu Diproses Admin</option>
                <option value="waiting_kasir">Menunggu Konfirmasi Kasir</option>
                <option value="completed">Selesai / Ditolak</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Dari Tanggal</label>
            <input type="date" x-model="filterStartDate" class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 outline-none transition-all">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Sampai Tanggal</label>
            <input type="date" x-model="filterEndDate" class="w-full bg-gray-50 border border-gray-200 text-gray-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 outline-none transition-all">
        </div>
    </div>

    <!-- Unified List -->
    <div class="space-y-6">
        @forelse($allItems as $item)
            @php
                $isPending = ($item->normalized_status === 'menunggu_admin' || $item->normalized_status === 'menunggu_pusat');
                $isWaitingKasir = ($item->normalized_status === 'menunggu_konfirmasi_kasir' || $item->normalized_status === 'diproses_admin');
                $isCompleted = ($item->normalized_status === 'selesai' || $item->normalized_status === 'ditolak');
                
                $statusType = $isPending ? 'pending' : ($isWaitingKasir ? 'waiting_kasir' : 'completed');
            @endphp
            
            <div class="admin-req-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow" 
                 x-show="showCard('{{ $item->cabang_id }}', '{{ $statusType }}', '{{ $item->created_at->format('Y-m-d') }}')"
                 data-cabang-id="{{ $item->cabang_id }}"
                 data-cabang-name="{{ $item->cabang->nama_cabang }}"
                 data-status-type="{{ $statusType }}">
                
                @if(!$item->is_bantuan)
                    <!-- ============================== -->
                    <!-- REQUEST OPERASIONAL BIASA      -->
                    <!-- ============================== -->
                    <div class="p-5 border-b border-gray-50 flex flex-col md:flex-row justify-between md:items-center gap-4 bg-gray-50/50">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-md uppercase tracking-wide">
                                    Operasional
                                </span>
                                <h4 class="font-extrabold text-gray-800 text-lg">Request #{{ $item->id }} - {{ strtoupper(str_replace('_', ' ', $item->jenis_request)) }}</h4>
                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-3 font-medium mt-2">
                                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> {{ $item->created_at->format('d/m/Y H:i') }}</span>
                                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> {{ $item->cabang->nama_cabang }}</span>
                                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> {{ $item->kasir->name }}</span>
                            </div>
                            @if($item->keterangan)
                                <div class="text-xs text-amber-600 mt-2 font-medium bg-amber-50 inline-block px-3 py-1 rounded-full border border-amber-100">
                                    Catatan: "{{ $item->keterangan }}"
                                </div>
                            @endif
                        </div>
                        <div>
                            @if($isPending)
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Diproses
                                </span>
                            @elseif($isWaitingKasir)
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Menunggu Konfirmasi Kasir
                                </span>
                            @elseif($isCompleted)
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Selesai
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($isPending)
                        <div class="p-5">
                            <!-- ISI SALDO -->
                            @if(in_array($item->jenis_request, ['isi_saldo', 'saldo_digital']))
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                                    <h5 class="font-bold text-slate-800 mb-3 text-sm">Detail Request Isi Saldo</h5>
                                    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-5 text-sm">
                                        <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm flex-1">
                                            <span class="text-slate-500 block text-xs mb-1">Layanan Tujuan</span>
                                            <span class="font-bold text-slate-800">{{ $item->masterAplikasiLayanan->nama_layanan ?? '-' }}</span>
                                        </div>
                                        <div class="bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm flex-1">
                                            <span class="text-slate-500 block text-xs mb-1">Nominal Diminta</span>
                                            <span class="font-bold text-blue-600 text-lg">Rp {{ number_format($item->nominal_request, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    
                                    <form action="{{ route('admin.request_operasional.isi_saldo', $item->id) }}" method="POST">
                                        @csrf
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Dompet Pusat (Sumber Dana)</label>
                                                <select name="admin_dompet_id" required class="w-full bg-white border border-slate-300 text-slate-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 outline-none transition-all shadow-sm text-sm">
                                                    <option value="">-- Pilih Rekening/Dompet Admin --</option>
                                                    @foreach($dompetPusat as $dompet)
                                                        <option value="{{ $dompet->id }}">
                                                            {{ $dompet->masterAplikasiLayanan->nama_layanan }} ({{ $dompet->nama_akun }}) - Saldo: Rp {{ number_format($dompet->saldo_sekarang, 0, ',', '.') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Biaya Admin (Modal Ekstra)</label>
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                                    </div>
                                                    <input type="number" name="biaya_admin_modal" value="0" min="0" required class="w-full bg-white border border-slate-300 text-slate-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 pl-10 outline-none transition-all shadow-sm text-sm">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 mt-6">
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-colors text-sm flex items-center gap-2" onclick="return confirm('Pusat akan memotong saldo. Lanjutkan?');">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Proses & Transfer Saldo
                                            </button>
                                            <button type="button" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold py-2.5 px-6 rounded-lg transition-colors text-sm" onclick="event.preventDefault(); if(confirm('Tolak request ini?')) document.getElementById('form-tolak-isi-{{$item->id}}').submit();">Tolak Request</button>
                                        </div>
                                    </form>
                                    <form id="form-tolak-isi-{{$item->id}}" action="{{ route('admin.request_operasional.tolak', $item->id) }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                </div>

                            @elseif(in_array($item->jenis_request, ['restok_barang', 'barang_fisik']))
                                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5">
                                    <h5 class="font-bold text-emerald-800 mb-3 flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        Request Restok Barang Fisik
                                    </h5>
                                    <div class="bg-white rounded-lg border border-emerald-100 p-4 mb-4 shadow-sm">
                                        <ul class="space-y-2 text-sm text-slate-700">
                                            @foreach($item->detail as $dtl)
                                                <li class="flex justify-between items-center pb-2 border-b border-gray-100 last:border-0 last:pb-0">
                                                    <span>{{ $dtl->barangFisik->nama_barang ?? 'Unknown' }}</span>
                                                    <span class="font-bold bg-emerald-100 text-emerald-800 py-1 px-3 rounded-full text-xs">Minta: {{ $dtl->nominal_atau_qty }} qty</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        @php
                                            $firstDtl = $item->detail->first();
                                            $mutasiUrl = route('distribusi.mutasi.index') . "?tab=mutasi&request_id={$item->id}&cabang_id={$item->cabang_id}";
                                            $poUrl = route('admin.pembelian.create') . "?request_id={$item->id}&cabang_id={$item->cabang_id}";
                                            if ($firstDtl) {
                                                $mutasiUrl .= "&barang_id={$firstDtl->barang_fisik_id}&qty={$firstDtl->nominal_atau_qty}";
                                                $poUrl .= "&barang_id={$firstDtl->barang_fisik_id}&qty={$firstDtl->nominal_atau_qty}";
                                            }
                                        @endphp
                                        
                                        <!-- Opsi 1: Penuhi dari Stok Gudang Pusat / Cabang lain via Mutasi -->
                                        <a href="{{ $mutasiUrl }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all text-sm flex items-center gap-2 active:scale-95">
                                            <i class="fa-solid fa-truck-fast text-xs"></i>
                                            <span>Kirim via Mutasi Stok</span>
                                        </a>

                                        <!-- Opsi 2: Beli Baru ke Supplier via PO jika Stok Gudang Sedang Menipis -->
                                        <a href="{{ $poUrl }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all text-sm flex items-center gap-2 active:scale-95" title="Pesan langsung ke vendor supplier untuk memenuhi permintaan cabang ini">
                                            <i class="fa-solid fa-cart-plus text-xs"></i>
                                            <span>Beli ke Supplier (PO)</span>
                                        </a>
                                        
                                        <!-- Opsi 3: Tolak Request -->
                                        <form action="{{ route('admin.request_operasional.tolak', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 font-bold py-2.5 px-4 rounded-xl transition text-sm cursor-pointer" onclick="return confirm('Tolak request restok barang ini?');">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            @elseif(in_array($item->jenis_request, ['restok_cash', 'cash_operasional']))
                                <div class="bg-purple-50 border border-purple-100 rounded-xl p-5">
                                    <h5 class="font-bold text-purple-800 mb-3 flex items-center gap-2 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        Request Restok Cash (Uang Fisik Laci)
                                    </h5>
                                    <div class="bg-white rounded-lg border border-purple-100 p-4 mb-5 shadow-sm inline-block">
                                        <span class="text-slate-500 block text-xs mb-1">Nominal Diminta</span>
                                        <span class="font-bold text-purple-700 text-xl">Rp {{ number_format($item->nominal_request, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex gap-3">
                                        @php
                                            $redirectUrl = route('distribusi.mutasi.index') . "?request_id={$item->id}&cabang_id={$item->cabang_id}&mutasi_uang=1&nominal={$item->nominal_request}";
                                        @endphp
                                        <a href="{{ $redirectUrl }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-colors text-sm flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                            Setujui (Ke Form Mutasi Uang)
                                        </a>
                                        
                                        <form action="{{ route('admin.request_operasional.tolak', $item->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-white hover:bg-red-50 text-red-600 border border-red-200 font-bold py-2.5 px-6 rounded-lg transition-colors text-sm" onclick="return confirm('Tolak request restok uang fisik ini?');">Tolak Request</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Tampilan untuk yang sudah diproses atau selesai -->
                        <div class="p-5">
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <p class="text-sm text-gray-600 font-medium">Telah diproses oleh Admin.</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        @if($isWaitingKasir) Sedang menunggu kasir <span class="font-bold">{{ $item->cabang->nama_cabang }}</span> melakukan konfirmasi penerimaan di sistemnya.
                                        @elseif($isCompleted) Kasir telah mengonfirmasi penerimaan (Selesai).
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                
                @else
                    <!-- ============================== -->
                    <!-- BANTUAN TRANSAKSI DIGITAL B    -->
                    <!-- ============================== -->
                    <div class="p-5 border-b border-cyan-100 flex flex-col md:flex-row justify-between md:items-center gap-4 bg-cyan-50/30">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <span class="bg-cyan-100 text-cyan-800 text-xs font-bold px-2.5 py-0.5 rounded-md uppercase tracking-wide flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    Digital B
                                </span>
                                <h4 class="font-extrabold text-cyan-900 text-lg">Bantuan Transaksi #{{ $item->id }}</h4>
                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-3 font-medium mt-2">
                                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> {{ $item->created_at->format('d/m/Y H:i') }}</span>
                                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg> {{ $item->cabang->nama_cabang }}</span>
                                <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> {{ $item->kasir->name }}</span>
                            </div>
                        </div>
                        <div>
                            @if($isPending)
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Diproses
                                </span>
                            @elseif($isWaitingKasir)
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Menunggu Konfirmasi
                                </span>
                            @elseif($item->normalized_status === 'selesai')
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Selesai
                                </span>
                            @elseif($item->normalized_status === 'ditolak')
                                <span class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Ditolak
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($isPending)
                        <div class="p-5 bg-cyan-50/10">
                            <div class="flex flex-col md:flex-row md:items-center gap-6 mb-6">
                                <div class="flex-1 space-y-1 text-sm bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                                    <p class="text-slate-500 text-xs uppercase tracking-wider">Produk Tujuan</p>
                                    <p class="font-bold text-slate-800 text-base">{{ $item->barang->nama_barang ?? '-' }}</p>
                                </div>
                                <div class="flex-1 space-y-1 text-sm bg-red-50 p-3 rounded-xl border border-red-100 shadow-sm">
                                    <p class="text-red-500 font-bold text-xs uppercase tracking-wider">Tujuan No / Rek</p>
                                    <p class="font-bold text-red-700 text-lg tracking-wide">{{ $item->nomor_tujuan }}</p>
                                </div>
                            </div>
                            
                            @if($item->keterangan)
                                <div class="bg-amber-50 border border-amber-100 p-3 rounded-lg text-sm text-amber-700 mb-6 flex items-start gap-2 font-medium">
                                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p>Catatan dari Cabang: "{{ $item->keterangan }}"</p>
                                </div>
                            @endif
                            
                            <div class="bg-white border border-gray-200 rounded-xl p-5 mb-6 shadow-sm">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-slate-500 text-sm font-bold uppercase tracking-wider">Nominal Topup/Transfer</span>
                                    <span class="text-2xl font-black text-cyan-600">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                                </div>
                                <p class="text-xs text-slate-400 italic">Total tagihan ke pelanggan akan dihitung otomatis oleh sistem berdasarkan Harga Modal yang Anda masukkan di bawah.</p>
                            </div>

                            <form action="{{ route('admin.request_operasional.bantuan_transaksi', $item->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Sumber Dana</label>
                                        <select name="admin_dompet_id" required class="w-full bg-white border border-slate-300 text-slate-700 rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block p-2.5 outline-none transition-all text-sm shadow-sm">
                                            <option value="">-- Rekening/Dompet Admin --</option>
                                            @foreach($dompetPusat as $dompet)
                                                <option value="{{ $dompet->id }}">
                                                    {{ $dompet->masterAplikasiLayanan->nama_layanan }} ({{ $dompet->nama_akun }}) - Saldo: Rp {{ number_format($dompet->saldo_sekarang, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Harga Modal Pusat</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input type="number" name="biaya_admin_modal" value="{{ $item->amount }}" min="0" required class="w-full bg-white border border-slate-300 text-slate-700 rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block p-2.5 pl-10 outline-none transition-all text-sm shadow-sm">
                                        </div>
                                    </div>
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Bukti Struk (Opsional)</label>
                                        <input type="file" name="bukti_foto" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100 transition-all cursor-pointer">
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-colors text-sm flex items-center gap-2" onclick="return confirm('Apakah Anda sudah mentransfernya ke tujuan pelanggan? Saldo pusat akan dipotong.');">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        Eksekusi Transaksi
                                    </button>
                                    <button type="button" class="bg-white hover:bg-red-50 text-red-600 border border-red-200 font-bold py-2.5 px-6 rounded-lg transition-colors text-sm" onclick="event.preventDefault(); if(confirm('Tolak request ini?')) document.getElementById('form-tolak-bantuan-{{$item->id}}').submit();">Tolak Request</button>
                                </div>
                            </form>
                            <form id="form-tolak-bantuan-{{$item->id}}" action="{{ route('admin.request_operasional.bantuan_transaksi_tolak', $item->id) }}" method="POST" class="hidden">
                                @csrf
                            </form>
                        </div>
                    @else
                        <div class="p-5">
                            <div class="bg-gray-50 border border-gray-200 p-5 rounded-xl">
                                <h6 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Ringkasan Pemrosesan</h6>
                                <div class="flex gap-8">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Modal Pusat</p>
                                        <p class="font-bold text-gray-800">Rp {{ number_format($item->harga_modal_pusat, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Tagihan ke Pelanggan</p>
                                        <p class="font-bold text-green-600">Rp {{ number_format($item->total_bayar_pelanggan, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">Semua Tenang & Aman</h3>
                <p class="text-sm text-gray-500">Belum ada request bantuan atau restok operasional saat ini.</p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('requestManager', () => ({
            filterCabang: '',
            filterStatus: '',
            filterStartDate: '',
            filterEndDate: '',
            
            pendingCounts: {},
            branchNames: {},
            
            initData() {
                // Hitung jumlah pending per cabang dengan membaca atribut dari elemen DOM
                const cards = document.querySelectorAll('.admin-req-card');
                let counts = {};
                let names = {};
                
                cards.forEach(card => {
                    let cabangId = card.getAttribute('data-cabang-id');
                    let cabangName = card.getAttribute('data-cabang-name');
                    let statusType = card.getAttribute('data-status-type');
                    
                    if (statusType === 'pending') {
                        if (!counts[cabangId]) {
                            counts[cabangId] = 0;
                            names[cabangId] = cabangName;
                        }
                        counts[cabangId]++;
                    }
                });
                
                this.pendingCounts = counts;
                this.branchNames = names;
            },
            
            showCard(cabangId, statusType, dateStr) {
                if (this.filterCabang !== '' && this.filterCabang !== cabangId) return false;
                if (this.filterStatus !== '' && this.filterStatus !== statusType) return false;
                
                if (this.filterStartDate !== '') {
                    if (dateStr < this.filterStartDate) return false;
                }
                if (this.filterEndDate !== '') {
                    if (dateStr > this.filterEndDate) return false;
                }
                
                return true;
            }
        }));
    });
</script>
@endpush
@endsection
