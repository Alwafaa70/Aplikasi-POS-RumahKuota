@extends('layouts.app')

@section('title', 'Riwayat Saldo APK')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<div class="container mx-auto px-4 py-8 max-w-5xl">
    
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('owner.gudang.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-800">Riwayat Mutasi Saldo APK</h1>
            <p class="text-gray-500 text-sm">Menampilkan jejak keluar masuk uang pada aplikasi layanan.</p>
        </div>
    </div>

    <!-- Informasi Rekening -->
    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg p-6 mb-8 text-white flex flex-col md:flex-row justify-between items-center">
        <div>
            <p class="text-purple-100 text-sm font-bold uppercase tracking-widest mb-1">{{ ucfirst($type) }}</p>
            <h2 class="text-3xl font-black">{{ $namaLayanan }}</h2>
            <p class="text-purple-50 mt-1 font-mono">No. Rekening: {{ $apkModel->nomor_rekening ?: 'Tidak Ada' }}</p>
        </div>
        <div class="mt-4 md:mt-0 text-right">
            <p class="text-purple-100 text-sm font-bold uppercase tracking-widest mb-1">Saldo Saat Ini</p>
            <h2 class="text-4xl font-black">Rp {{ number_format($apkModel->saldo_sekarang, 0, ',', '.') }}</h2>
        </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-4">Waktu</th>
                        <th scope="col" class="px-6 py-4">Keterangan</th>
                        <th scope="col" class="px-6 py-4 text-right">Nominal</th>
                        <th scope="col" class="px-6 py-4 text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $r)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                {{ \Carbon\Carbon::parse($r->created_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $r->keterangan }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold {{ $r->jenis_mutasi === 'masuk' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $r->jenis_mutasi === 'masuk' ? '+' : '-' }} Rp {{ number_format($r->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-black text-gray-700">
                                Rp {{ number_format($r->saldo_akhir, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                Belum ada riwayat transaksi pada APK ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riwayat->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
