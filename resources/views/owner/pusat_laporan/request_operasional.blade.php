@extends('layouts.app')
@section('title', 'Riwayat Request Operasional')

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
                <h1 class="text-2xl font-black text-gray-800">Riwayat Request Operasional</h1>
                <p class="text-sm text-gray-500">Log permintaan operasional (isi saldo, restok kas, dll) dari cabang ke gudang pusat (Read-Only).</p>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-4">
        <form action="{{ route('owner.laporan_pusat.request_operasional') }}" method="GET" class="flex flex-wrap items-end gap-3 w-full flex-1">
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
                <a href="{{ route('owner.laporan_pusat.request_operasional') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
        <div class="flex gap-2">
            <a href="{{ route('owner.laporan_pusat.request_operasional.pdf') }}?cabang={{ request('cabang_id') }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" target="_blank" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-red-600 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg> PDF
            </a>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 border border-gray-200">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Waktu</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Cabang & Kasir</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Jenis Request</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Nominal / Barang</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 border-r border-gray-200">{{ \Carbon\Carbon::parse($req->created_at)->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">
                            <div class="font-bold text-gray-800">{{ $req->cabang->nama_cabang ?? '-' }}</div>
                            <div class="text-xs text-gray-500">Oleh: {{ $req->kasir->name ?? 'Sistem' }}</div>
                        </td>
                        <td class="px-6 py-4 border-r border-gray-200 font-bold text-indigo-600 uppercase text-xs">
                            {{ str_replace('_', ' ', $req->jenis_request) }}
                        </td>
                        <td class="px-6 py-4 border-r border-gray-200">
                            @if(in_array($req->jenis_request, ['isi_saldo', 'restok_cash', 'bantuan_transaksi', 'saldo_digital']))
                                <span class="font-bold text-gray-800">Rp {{ number_format($req->nominal_request, 0, ',', '.') }}</span>
                            @else
                                @forelse($req->detail as $dtl)
                                    <div class="mb-1">
                                        <span class="font-medium text-gray-700">{{ $dtl->barangFisik->nama_barang ?? 'Barang' }}</span> 
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs font-bold">{{ $dtl->nominal_atau_qty ?? 0 }} Qty</span>
                                    </div>
                                @empty
                                    <span class="text-gray-400 italic text-xs">Tidak ada detail</span>
                                @endforelse
                            @endif
                        </td>
                        <td class="px-6 py-4 border-r border-gray-200">
                            @if($req->status_request === 'selesai')
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">Selesai</span>
                            @elseif($req->status_request === 'ditolak')
                                <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded">Ditolak</span>
                            @else
                                <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded">Menunggu</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada histori request operasional.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $requests->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
