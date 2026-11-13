@extends('layouts.app')
@section('title', 'Riwayat Mutasi & Distribusi')

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
                <h1 class="text-2xl font-black text-gray-800">Riwayat Mutasi Stok</h1>
                <p class="text-sm text-gray-500">Log mutasi dan distribusi stok barang antar cabang atau dari gudang pusat (Read-Only).</p>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-4">
        <form action="{{ route('owner.laporan_pusat.mutasi') }}" method="GET" class="flex flex-wrap items-end gap-3 w-full">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Filter Cabang</label>
                <select name="cabang_id" class="border-2 border-gray-200 rounded-lg p-2 text-sm focus:border-indigo-500 outline-none min-w-[150px]">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}" {{ request('cabang_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700 transition">Filter Data</button>
            @if(request('start_date') || request('end_date') || request('cabang_id'))
                <a href="{{ route('owner.laporan_pusat.mutasi') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 border border-gray-200">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">ID Mutasi</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Tanggal Mutasi</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Asal -> Tujuan</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Petugas</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Status</th>
                        <th scope="col" class="px-6 py-3 text-center">Detail Barang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasis as $mutasi)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold text-blue-600 border-r border-gray-200">MTS-{{ str_pad($mutasi->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ \Carbon\Carbon::parse($mutasi->tanggal_mutasi)->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">
                            <div class="flex items-center gap-2">
                                <span class="bg-gray-100 px-2 py-1 rounded text-xs font-bold">{{ $mutasi->cabangAsal->nama_cabang ?? 'Gudang Pusat' }}</span>
                                <span>➔</span>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-bold">{{ $mutasi->cabangTujuan->nama_cabang ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 border-r border-gray-200">{{ $mutasi->admin->name ?? 'Sistem' }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">
                            @if($mutasi->status_mutasi === 'selesai' || $mutasi->status_mutasi === 'diterima')
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">Selesai / Diterima</span>
                            @elseif($mutasi->status_mutasi === 'dikirim')
                                <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded">Dikirim</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2 py-1 rounded">{{ ucfirst($mutasi->status_mutasi) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="document.getElementById('modal-{{ $mutasi->id }}').classList.remove('hidden')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-xs font-bold hover:bg-blue-200 transition">Lihat</button>
                            
                            <!-- MODAL DETAIL -->
                            <div id="modal-{{ $mutasi->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity text-left">
                                <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl overflow-hidden m-4 relative">
                                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                                        <h3 class="font-black text-lg text-gray-800">Rincian Mutasi MTS-{{ str_pad($mutasi->id, 5, '0', STR_PAD_LEFT) }}</h3>
                                        <button onclick="document.getElementById('modal-{{ $mutasi->id }}').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl leading-none">&times;</button>
                                    </div>
                                    <div class="p-6 overflow-y-auto max-h-[60vh]">
                                        <table class="w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden">
                                            <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase font-bold">
                                                <tr>
                                                    <th class="px-4 py-2 border-b border-r border-indigo-100">Nama Barang</th>
                                                    <th class="px-4 py-2 border-b border-indigo-100 text-center">Jumlah (Qty)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($mutasi->detailMutasi as $detail)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 border-b border-r border-gray-100 font-medium text-gray-700">{{ $detail->barang->nama_barang ?? 'Barang Tidak Ditemukan' }}</td>
                                                    <td class="px-4 py-2 border-b border-gray-100 text-center font-bold">{{ $detail->jumlah }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-right">
                                        <button onclick="document.getElementById('modal-{{ $mutasi->id }}').classList.add('hidden')" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg font-bold shadow transition">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada histori mutasi stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $mutasis->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
