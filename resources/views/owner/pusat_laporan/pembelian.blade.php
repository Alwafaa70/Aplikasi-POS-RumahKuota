@extends('layouts.app')
@section('title', 'Riwayat Pembelian PO')

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
            <a href="{{ route('owner.laporan_pusat.index') }}" class="bg-gray-100 p-2 rounded-lg text-gray-600 hover:bg-gray-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800">Riwayat Pembelian PO</h1>
                <p class="text-sm text-gray-500">Log pembelian barang ke supplier yang dilakukan Admin Pusat (Read-Only).</p>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-4">
        <form action="{{ route('owner.laporan_pusat.pembelian') }}" method="GET" class="flex flex-wrap items-end gap-3 w-full md:w-auto flex-1">
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
                <a href="{{ route('owner.laporan_pusat.pembelian') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
        <div class="flex gap-2">
            <a href="{{ route('owner.laporan_pusat.pembelian.export_pdf') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" target="_blank" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-red-600 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> PDF
            </a>
            <a href="{{ route('owner.laporan_pusat.pembelian.export_excel') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-green-700 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> Excel
            </a>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 border border-gray-200">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">No. PO</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Tanggal</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Supplier</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Kontak (Email/No.HP)</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Petugas</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Total Harga</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Status</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembelians as $beli)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold text-blue-600 border-r border-gray-200">PO-{{ str_pad($beli->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ \Carbon\Carbon::parse($beli->tanggal_pembelian)->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ $beli->supplier->nama_supplier ?? 'Tanpa Supplier' }}</td>
                        <td class="px-6 py-4 border-r border-gray-200 text-gray-500">{{ $beli->supplier->kontak ?? '-' }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ $beli->admin->name ?? 'Sistem' }}</td>
                        <td class="px-6 py-4 border-r border-gray-200 font-bold text-gray-800">Rp {{ number_format($beli->total_harga, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">
                            @if($beli->status === 'selesai')
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">Selesai (Gudang)</span>
                            @elseif($beli->status === 'dalam_perjalanan')
                                <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded">Di Perjalanan</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2 py-1 rounded">Draft</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="document.getElementById('modal-{{ $beli->id }}').classList.remove('hidden')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-xs font-bold hover:bg-blue-200 transition">Detail</button>
                            
                            <!-- MODAL DETAIL -->
                            <div id="modal-{{ $beli->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity text-left">
                                <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl overflow-hidden m-4 relative">
                                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                                        <h3 class="font-black text-lg text-gray-800">Detail Pembelian PO-{{ str_pad($beli->id, 5, '0', STR_PAD_LEFT) }}</h3>
                                        <button onclick="document.getElementById('modal-{{ $beli->id }}').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl leading-none">&times;</button>
                                    </div>
                                    <div class="p-6 overflow-y-auto max-h-[60vh]">
                                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                                            <div>
                                                <p class="text-gray-500 text-xs">Supplier</p>
                                                <p class="font-bold text-gray-800">{{ $beli->supplier->nama_supplier ?? 'Tanpa Supplier' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500 text-xs">Petugas / Admin</p>
                                                <p class="font-bold text-gray-800">{{ $beli->admin->name ?? 'Sistem' }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500 text-xs">Metode Pembayaran</p>
                                                <p class="font-bold text-gray-800">{{ strtoupper(str_replace('_', ' ', $beli->metode_pembayaran)) }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500 text-xs">Tanggal Transaksi</p>
                                                <p class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($beli->tanggal_pembelian)->translatedFormat('d F Y H:i') }}</p>
                                            </div>
                                        </div>
                                        
                                        <table class="w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden">
                                            <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase font-bold">
                                                <tr>
                                                    <th class="px-4 py-2 border-b border-r border-indigo-100">Nama Barang</th>
                                                    <th class="px-4 py-2 border-b border-r border-indigo-100 text-center">Qty</th>
                                                    <th class="px-4 py-2 border-b border-r border-indigo-100 text-right">Harga Satuan</th>
                                                    <th class="px-4 py-2 border-b border-indigo-100 text-right">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($beli->detailPembelian as $detail)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 border-b border-r border-gray-100 font-medium text-gray-700">{{ $detail->barangFisik->nama_barang ?? 'Barang Tidak Ditemukan' }}</td>
                                                    <td class="px-4 py-2 border-b border-r border-gray-100 text-center font-bold">{{ $detail->qty }}</td>
                                                    <td class="px-4 py-2 border-b border-r border-gray-100 text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                                    <td class="px-4 py-2 border-b border-gray-100 text-right font-bold text-indigo-700">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                                <tr>
                                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-600 border-r border-gray-200">TOTAL BELANJA</td>
                                                    <td class="px-4 py-3 text-right font-black text-lg text-gray-800">Rp {{ number_format($beli->total_harga, 0, ',', '.') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-right">
                                        <button onclick="document.getElementById('modal-{{ $beli->id }}').classList.add('hidden')" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg font-bold shadow transition">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">Belum ada data histori pembelian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $pembelians->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
