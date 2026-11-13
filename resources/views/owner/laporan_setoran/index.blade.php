@extends('layouts.app')

@section('title', 'Laporan Riwayat Setoran')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<div class="container mx-auto px-4 py-8 max-w-7xl">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">Laporan Riwayat Setoran</h1>
            <p class="text-gray-500 mt-1">Rekapitulasi setoran dari Cabang ke Pusat dan dari Pusat ke Owner.</p>
        </div>
        <div class="flex gap-2">
            <!-- Akan ditambahkan fitur export nantinya jika diperlukan -->
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
        <form method="GET" action="{{ route('owner.laporan_setoran.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Jenis Setoran</label>
                <select name="jenis" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">Semua</option>
                    <option value="cabang_ke_pusat" {{ request('jenis') == 'cabang_ke_pusat' ? 'selected' : '' }}>Cabang ke Pusat</option>
                    <option value="pusat_ke_owner" {{ request('jenis') == 'pusat_ke_owner' ? 'selected' : '' }}>Pusat ke Owner</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition text-sm">
                    Filter
                </button>
                <a href="{{ route('owner.laporan_setoran.index') }}" class="w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg transition text-sm">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th scope="col" class="px-6 py-4">Waktu</th>
                        <th scope="col" class="px-6 py-4">Jenis Setoran</th>
                        <th scope="col" class="px-6 py-4">Dari / Oleh</th>
                        <th scope="col" class="px-6 py-4">Keterangan</th>
                        <th scope="col" class="px-6 py-4 text-right">Nominal</th>
                        <th scope="col" class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($setoran as $s)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($s->created_at)->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($s->cabang_id)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Cabang â†’ Pusat
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Pusat â†’ Owner
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($s->cabang_id)
                                    <div class="font-bold text-gray-900">{{ $s->cabang->nama_cabang }}</div>
                                    <div class="text-xs text-gray-500">{{ $s->laporanClosing->kasir->name ?? 'Kasir' }}</div>
                                @else
                                    <div class="font-bold text-gray-900">Admin Pusat</div>
                                    <div class="text-xs text-gray-500">{{ $s->admin->name ?? 'Admin' }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ ucfirst($s->jenis_setoran) }}</div>
                                @if($s->jenis_setoran === 'digital' && $s->pusatAplikasiLayanan)
                                    <div class="text-xs text-gray-500">{{ $s->pusatAplikasiLayanan->masterAplikasiLayanan->nama_layanan ?? '' }}</div>
                                @endif
                                <div class="text-xs text-gray-400 mt-1">{{ $s->catatan }}</div>
                            </td>
                            <td class="px-6 py-4 text-right font-black text-gray-800">
                                Rp {{ number_format($s->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($s->status === 'menunggu_konfirmasi')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Menunggu</span>
                                @elseif($s->status === 'diterima')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Diterima</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Ditolak</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data riwayat setoran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($setoran->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $setoran->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
