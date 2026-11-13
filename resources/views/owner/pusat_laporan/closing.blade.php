@extends('layouts.app')
@section('title', 'Riwayat Laporan Closing')

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
                <h1 class="text-2xl font-black text-gray-800">Riwayat Laporan Closing</h1>
                <p class="text-sm text-gray-500">Log penyelesaian shift (closing) harian dari setiap cabang (Read-Only).</p>
            </div>
        </div>
    </div>

    <!-- FILTER -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center gap-4">
        <form action="{{ route('owner.laporan_pusat.closing') }}" method="GET" class="flex flex-wrap items-end gap-3 w-full flex-1">
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
                <a href="{{ route('owner.laporan_pusat.closing') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
        <div class="flex gap-2">
            <a href="{{ route('owner.laporan_pusat.closing.pdf') }}?cabang_id={{ request('cabang_id') }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}" target="_blank" class="bg-red-500 text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-red-600 transition flex items-center gap-2">
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
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Tanggal Closing</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Cabang & Kasir</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200 text-right">Total Uang Fisik</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200 text-right">Total Saldo Digital</th>
                        <th scope="col" class="px-6 py-3 border-r border-gray-200">Status Konfirmasi</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($closings as $closing)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold text-gray-800 border-r border-gray-200">{{ \Carbon\Carbon::parse($closing->tanggal)->translatedFormat('d M Y') }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">
                            <div class="font-bold text-indigo-600">{{ $closing->cabang->nama_cabang ?? '-' }}</div>
                            <div class="text-xs text-gray-500">Kasir: {{ $closing->kasir->name ?? 'Sistem' }}</div>
                        </td>
                        <td class="px-6 py-4 border-r border-gray-200 text-right font-bold text-gray-800">Rp {{ number_format($closing->total_uang_fisik, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 border-r border-gray-200 text-right font-bold text-gray-800">Rp {{ number_format($closing->total_saldo_digital, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 border-r border-gray-200">
                            @if($closing->is_confirmed)
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">Dikonfirmasi Admin</span>
                            @else
                                <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded">Menunggu Konfirmasi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="document.getElementById('modal-{{ $closing->id }}').classList.remove('hidden')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded text-xs font-bold hover:bg-blue-200 transition">Rincian</button>
                            
                            <!-- MODAL DETAIL -->
                            <div id="modal-{{ $closing->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm transition-opacity text-left">
                                <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl overflow-hidden m-4 relative">
                                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                                        <h3 class="font-black text-lg text-gray-800">Rincian Closing: {{ $closing->cabang->nama_cabang ?? '-' }} ({{ \Carbon\Carbon::parse($closing->tanggal)->translatedFormat('d M Y') }})</h3>
                                        <button onclick="document.getElementById('modal-{{ $closing->id }}').classList.add('hidden')" class="text-gray-400 hover:text-red-500 font-bold text-xl leading-none">&times;</button>
                                    </div>
                                    <div class="p-6 overflow-y-auto max-h-[60vh]">
                                        
                                        <h4 class="font-bold text-gray-700 mb-2 mt-4 uppercase text-xs border-b pb-2">Rincian Saldo Aplikasi Digital</h4>
                                        <table class="w-full text-sm text-left border border-gray-200 rounded-lg overflow-hidden mb-6">
                                            <thead class="bg-indigo-50 text-indigo-800 text-xs uppercase font-bold">
                                                <tr>
                                                    <th class="px-4 py-2 border-b border-r border-indigo-100">Nama Aplikasi / Layanan</th>
                                                    <th class="px-4 py-2 border-b border-indigo-100 text-right">Saldo Akhir (Dilaporkan)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($closing->details as $detail)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-2 border-b border-r border-gray-100 font-medium text-gray-700">{{ $detail->masterAplikasiLayanan->nama_layanan ?? 'Aplikasi Digital' }}</td>
                                                    <td class="px-4 py-2 border-b border-gray-100 text-right font-bold text-gray-800">Rp {{ number_format($detail->saldo_akhir, 0, ',', '.') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="2" class="px-4 py-4 text-center text-gray-500 text-xs italic">Tidak ada rincian saldo digital dilaporkan.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        
                                        @if($closing->keterangan)
                                        <div class="mt-4 p-4 bg-yellow-50 rounded-lg border border-yellow-100">
                                            <p class="text-xs text-yellow-800 font-bold mb-1">Catatan Kasir:</p>
                                            <p class="text-sm text-gray-700 italic">"{{ $closing->keterangan }}"</p>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-right">
                                        <button onclick="document.getElementById('modal-{{ $closing->id }}').classList.add('hidden')" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg font-bold shadow transition">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada histori laporan closing.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $closings->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
