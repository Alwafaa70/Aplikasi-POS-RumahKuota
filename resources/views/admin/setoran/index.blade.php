@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-black text-gray-800">Manajemen Setoran Cabang</h1>
            <p class="text-gray-500 text-sm mt-1">Verifikasi uang fisik dari hasil closing kasir cabang.</p>
        </div>
        
        <!-- Saldo Kas Pusat Widget -->
        <div class="bg-gradient-to-r from-green-600 to-green-800 rounded-xl p-4 shadow-lg flex items-center gap-4 text-white">
            <div class="bg-white/20 p-3 rounded-lg backdrop-blur-sm">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-green-100 uppercase tracking-wider">Total Kas Pusat (Fisik)</p>
                <h2 class="text-2xl font-black">Rp {{ number_format($totalKas, 0, ',', '.') }}</h2>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r shadow-sm flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r shadow-sm flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal & Cabang</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kasir Closing</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Nominal Setoran</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($setorans as $setoran)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-gray-900">{{ $setoran->laporanClosing->tanggal }}</div>
                            <div class="text-sm text-blue-600 font-medium">{{ $setoran->cabang->nama_cabang }}</div>
                            <div class="text-xs text-gray-400">{{ $setoran->created_at->format('H:i') }} WIB</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">{{ $setoran->laporanClosing->kasir->name }}</div>
                            <div class="text-xs text-gray-500">
                                Selisih Closing: 
                                <span class="{{ $setoran->laporanClosing->selisih_fisik < 0 ? 'text-red-600 font-bold' : ($setoran->laporanClosing->selisih_fisik > 0 ? 'text-green-600 font-bold' : 'text-gray-500') }}">
                                    Rp {{ number_format($setoran->laporanClosing->selisih_fisik, 0, ',', '.') }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-lg font-black text-green-600">Rp {{ number_format($setoran->nominal, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($setoran->status === 'menunggu_konfirmasi')
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full border border-yellow-300">
                                    ⏳ Menunggu Penjemputan
                                </span>
                            @elseif($setoran->status === 'diterima')
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full border border-green-300">
                                    ✅ Diterima Pusat
                                </span>
                                <div class="text-[10px] text-gray-500 mt-1">oleh {{ $setoran->admin->name ?? 'Admin' }}</div>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full border border-red-300">
                                    ❌ Ditolak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($setoran->status === 'menunggu_konfirmasi')
                                <form action="{{ route('admin.setoran.confirm', $setoran->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Apakah Anda yakin sudah menerima fisik uang sejumlah Rp {{ number_format($setoran->nominal, 0, ',', '.') }} dari {{ $setoran->cabang->nama_cabang }}?\n\nUang ini akan ditambahkan ke Kas Pusat.')" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Konfirmasi Terima
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400 text-xs font-bold italic">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-flex justify-center items-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Belum ada Setoran</h3>
                            <p class="text-gray-500 text-sm">Cabang belum melakukan closing hari ini atau semua setoran sudah dikonfirmasi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
