@extends('layouts.app')
@section('title', 'Audit Riwayat Transaksi Global')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar { z-index: 50; }
        .main-content { min-width: 0; }
    </style>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <!-- HEADER -->
    <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.gudang.index') }}" class="bg-gray-100 p-2 rounded-lg text-gray-600 hover:bg-gray-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800">Riwayat Transaksi Global</h1>
                <p class="text-sm text-gray-500">Log pencatatan Pembelian Pusat, Mutasi Cabang, dan Penjualan Kasir.</p>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-4">
        <form action="{{ route('owner.transaksi') }}" method="GET" class="flex items-end gap-3 w-full md:w-auto">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700 transition">Filter Data</button>
            @if(request('start_date') || request('end_date'))
                <a href="{{ route('owner.transaksi') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
    </div>

    <!-- TABS -->
    <div class="bg-white rounded-t-xl border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500" id="trxTab" data-tabs-toggle="#trxTabContent" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg aria-selected:text-blue-600 aria-selected:border-blue-600 hover:text-gray-600 hover:border-gray-300 transition" id="beli-tab" data-tabs-target="#beli" type="button" role="tab" aria-controls="beli" aria-selected="true">1. Pembelian Pusat (Masuk)</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg aria-selected:text-orange-600 aria-selected:border-orange-600 hover:text-gray-600 hover:border-gray-300 transition" id="mutasi-tab" data-tabs-target="#mutasi" type="button" role="tab" aria-controls="mutasi" aria-selected="false">2. Mutasi Stok (Perpindahan)</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg aria-selected:text-green-600 aria-selected:border-green-600 hover:text-gray-600 hover:border-gray-300 transition" id="jual-tab" data-tabs-target="#jual" type="button" role="tab" aria-controls="jual" aria-selected="false">3. Penjualan Cabang (Keluar)</button>
            </li>
        </ul>
    </div>

    <div id="trxTabContent" class="bg-white rounded-b-xl shadow-sm border border-t-0 border-gray-200 mb-8 p-6">
        
        <!-- TAB 1: PEMBELIAN -->
        <div class="hidden" id="beli" role="tabpanel" aria-labelledby="beli-tab">
            <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">No. Referensi</th>
                            <th scope="col" class="px-6 py-3">Tanggal / Waktu</th>
                            <th scope="col" class="px-6 py-3">Supplier</th>
                            <th scope="col" class="px-6 py-3">Petugas</th>
                            <th scope="col" class="px-6 py-3 text-right">Total Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembelian as $beli)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-blue-600">#{{ $beli->id }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($beli->tanggal_pembelian)->translatedFormat('d M Y') }}</td>
                            <td class="px-6 py-4">{{ $beli->supplier->nama_supplier ?? 'Tanpa Supplier' }}</td>
                            <td class="px-6 py-4">{{ $beli->adminPusat->name ?? 'Sistem' }}</td>
                            <td class="px-6 py-4 text-right font-black text-gray-800">Rp {{ number_format($beli->total_harga, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat pembelian ke Supplier.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: MUTASI -->
        <div class="hidden" id="mutasi" role="tabpanel" aria-labelledby="mutasi-tab">
            <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID Mutasi</th>
                            <th scope="col" class="px-6 py-3">Tanggal Mutasi</th>
                            <th scope="col" class="px-6 py-3">Tujuan Cabang</th>
                            <th scope="col" class="px-6 py-3">Dibuat Oleh</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasi as $mut)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-orange-600">MUT-{{ $mut->id }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($mut->created_at)->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 font-bold">{{ $mut->cabangTujuan->nama_cabang ?? 'Unknown' }}</td>
                            <td class="px-6 py-4">{{ $mut->adminPusat->name ?? 'Sistem' }}</td>
                            <td class="px-6 py-4">
                                @if($mut->status === 'dikirim')
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2.5 py-0.5 rounded border border-yellow-200">Dalam Perjalanan</span>
                                @else
                                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-0.5 rounded border border-green-200">Diterima Cabang</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat mutasi stok.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: PENJUALAN -->
        <div class="hidden" id="jual" role="tabpanel" aria-labelledby="jual-tab">
            <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nota</th>
                            <th scope="col" class="px-6 py-3">Waktu Penjualan</th>
                            <th scope="col" class="px-6 py-3">Cabang Lokasi</th>
                            <th scope="col" class="px-6 py-3">Kasir</th>
                            <th scope="col" class="px-6 py-3">Diskon</th>
                            <th scope="col" class="px-6 py-3">Metode Bayar</th>
                            <th scope="col" class="px-6 py-3 text-right">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penjualan as $jual)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-green-600">TRX-{{ str_pad($jual->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($jual->created_at)->translatedFormat('d M Y, H:i') }}</td>
                            <td class="px-6 py-4">{{ $jual->cabang->nama_cabang ?? 'Unknown' }}</td>
                            <td class="px-6 py-4">{{ $jual->kasir->name ?? 'Kasir' }}</td>
                            <td class="px-6 py-4">
                                @if($jual->total_diskon > 0)
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold border border-red-200" title="Diskon Diberikan">
                                        - Rp {{ number_format($jual->total_diskon, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 text-xs font-bold px-2.5 py-0.5 rounded border border-gray-200 uppercase">
                                    {{ $jual->metode_pembayaran }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-gray-800">Rp {{ number_format($jual->total_tagihan, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat penjualan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
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
